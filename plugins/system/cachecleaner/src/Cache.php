<?php
/**
 * @package         Cache Cleaner
 * @version         9.1.0
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            https://regularlabs.com
 * @copyright       Copyright © 2023 Regular Labs All Rights Reserved
 * @license         GNU General Public License version 2 or later
 */

namespace RegularLabs\Plugin\System\CacheCleaner;

defined('_JEXEC') or die;

use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Filesystem\File as JFile;
use Joomla\CMS\Filesystem\Folder as JFolder;
use Joomla\CMS\Http\HttpFactory as JHttpFactory;
use Joomla\CMS\Language\Text as JText;
use RegularLabs\Library\Document as RL_Document;

class Cache
{
    static $error        = '';
    static $message      = '';
    static $plugin_id    = 0;
    static $show_message = true;
    static $thirdparties = ['siteground', 'keycdn', 'cdn77', 'cloudflare'];

    public static function addError($error = '')
    {
        self::$error .= self::$error ? '<br>' : '';
        self::$error .= $error;
    }

    public static function addMessage($message = '')
    {
        self::$message .= self::$message ? '<br>' : '';
        self::$message .= $message;
    }

    public static function clean($plugin_id = 0)
    {

        self::$plugin_id = $plugin_id;

        if ( ! self::getCleanType())
        {
            return false;
        }

        // Run the main purge actions
        $result = self::purge();

        // only handle messages in html
        if ( ! RL_Document::isHtml())
        {
            return false;
        }

        if (JFactory::getApplication()->input->getInt('break'))
        {
            die(
            str_replace('<br>',
                ' - ',
                $result->error ?: '+' . $result->message
            )
            );
        }

        if (self::$show_message && $result->message)
        {
            JFactory::getApplication()->enqueueMessage(
                $result->error ?: $result->message,
                ($result->error ? 'error' : 'message')
            );
        }

        return true;
    }

    public static function getError()
    {
        return self::$error;
    }

    public static function setError($error = '')
    {
        self::$error = $error;
    }

    public static function getMessage()
    {
        return self::$message;
    }

    public static function setMessage($message = '')
    {
        self::$message = $message;
    }

    public static function getResult($show_size = null)
    {
        $show_size = ! is_null($show_size) ? $show_size : Params::get()->show_size;

        $result = (object) [
            'error'   => self::getError(),
            'message' => self::$message ?: JText::_('CC_CACHE_CLEANED'),
        ];

        if ($result->error)
        {
            $error = JText::_('CC_NOT_ALL_CACHE_COULD_BE_REMOVED');
            $error .= $result->error !== true ? '<br>' . $result->error : '';

            $result->error = $error;

            return $result;
        }

        if ( ! $show_size)
        {
            return $result;
        }

        $size = Cache\Cache::getSize();

        if ($size)
        {
            $result->message .= ' (' . $size . ')';
        }

        return $result;
    }

    public static function purge()
    {
        $params = Params::get();

        // Joomla cache
        if (self::passType('purge'))
        {
            Cache\Joomla::purge();
        }


        // Folders
        if (self::passType('clean_tmp'))
        {
            Cache\Folders::purge_tmp();
        }

        // Purge expired cache
        if (self::passType('purge'))
        {
            Cache\Joomla::purgeExpired();
        }

        // Purge update cache
        if (self::passType('purge_updates'))
        {
            Cache\Joomla::purgeUpdates();
        }

        // Purge disabled redirects
        if (self::passType('purge_disabled_redirects'))
        {
            Cache\Joomla::purgeDisabledRedirects();
        }

        // Global check-in
        if (self::passType('checkin'))
        {
            Cache\Joomla::checkIn();
        }


        return self::getResult();
    }

    public static function writeToLog($file_name, $error)
    {
        $params = Params::get();

        // Write current time to text file

        $file_path = str_replace('//', '/', JPATH_SITE . '/' . str_replace('\\', '/', $params->log_path . '/'));

        if ( ! JFolder::exists($file_path))
        {
            $file_path = JPATH_PLUGINS . '/system/cachecleaner/';
        }

        JFile::append(
            $file_path . 'cachecleaner_' . $file_name . '.log',
            '[' . date('Y-m-d H:i:s') . '] ' . $error
        );
    }

    private static function getCleanType()
    {
        $params = Params::get();

        $cleancache = trim(JFactory::getApplication()->input->getString('cleancache', ''));

        // Clean via url
        if ( ! empty($cleancache))
        {
            // Return if on frontend and no secret url key is given
            if (RL_Document::isClient('site') && $cleancache != $params->frontend_secret)
            {
                return '';
            }

            $user = JFactory::getApplication()->getIdentity() ?: JFactory::getUser();

            // Return if on login page
            if (RL_Document::isClient('administrator') && $user->get('guest'))
            {
                return '';
            }

            if (JFactory::getApplication()->input->getWord('src') == 'button')
            {
                return 'button';
            }

            self::$show_message = true;

            if (RL_Document::isClient('site') && $cleancache == $params->frontend_secret)
            {
                self::$show_message = $params->frontend_secret_msg;
            }

            return 'clean';
        }

        $is_plugin_page = self::isCacheCleanerSystemPluginPage();

        // Clean via save task
        if (self::passTask())
        {
            // Skip cleaning cache on the Cache Cleaner system plugin page
            if ($is_plugin_page && self::$show_message)
            {
                JFactory::getApplication()->enqueueMessage(
                    JText::_('CC_NO_CLEANING_ON_PLUGIN_PAGE'),
                    'message'
                );
            }

            return $is_plugin_page ? '' : 'save';
        }


        return '';
    }

    private static function isCacheCleanerSystemPluginPage()
    {
        $input = JFactory::getApplication()->input;

        return self::$plugin_id
            && $input->get('option') == 'com_plugins'
            && $input->get('view') == 'plugin'
            && $input->get('extension_id') == self::$plugin_id;
    }

    private static function passInterval()
    {
    }

    private static function passIntervalFile()
    {
    }

    private static function passTask()
    {
        $params = Params::get();
        $task   = JFactory::getApplication()->input->get('task', '');

        if ( ! $task)
        {
            return false;
        }

        $task = explode('.', $task, 2);
        $task = $task[1] ?? $task[0];

        if (str_starts_with($task, 'save'))
        {
            $task = 'save';
        }

        $tasks = array_diff(array_map('trim', explode(',', $params->auto_save_tasks)), ['']);

        if (empty($tasks) || ! in_array($task, $tasks))
        {
            return false;
        }

        if (RL_Document::isClient('administrator') && $params->auto_save_admin)
        {
            self::$show_message = $params->auto_save_admin_msg;

            return true;
        }

        if (RL_Document::isClient('site') && $params->auto_save_front)
        {
            self::$show_message = $params->auto_save_front_msg;

            return true;
        }

        return false;
    }

    private static function passType($type)
    {
        $params = Params::get();

        if (empty($params->{$type}))
        {
            return false;
        }

        if ($params->{$type} == 2 && self::getCleanType() != 'button')
        {
            return false;
        }

        return true;
    }

    private static function purgeThirdPartyCache($thirdparty)
    {
    }

    private static function purgeThirdPartyCacheByUrl()
    {
    }

    private static function purgeThirdPartyCaches()
    {
    }

    private static function queryUrl()
    {
    }

    private static function updateLog()
    {
    }
}
