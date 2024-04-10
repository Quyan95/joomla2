<?php

/**
 * @package         EngageBox
 * @version         6.1.4 Pro
 * 
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            http://www.tassos.gr
 * @copyright       Copyright © 2020 Tassos Marinos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

namespace EngageBox;

defined('_JEXEC') or die('Restricted access');

class Admin 
{
    /**
     *  Returns permissions
     *
     *  @return  object
     */
    public static function getActions()
    {
        $user = \JFactory::getUser();
        $result = new \JObject;
        $assetName = 'com_rstbox';

        $actions = array(
            'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.state', 'core.delete'
        );

        foreach ($actions as $action)
        {
            $result->set($action, $user->authorise($action, $assetName));
        }

        return $result;
    }

    public static function geoPluginNeedsUpdate()
    {
        // Check if TGeoIP plugin is enabled
        if (!\NRFramework\Extension::pluginIsEnabled('tgeoip'))
        {
            return;
        }

        $plugin_path = JPATH_PLUGINS . '/system/tgeoip/';

        // Load plugin language (Needed by Joomla 4)
        \JFactory::getLanguage()->load('plg_system_tgeoip', $plugin_path);

        // Load TGeoIP classes
        @include_once $plugin_path . 'vendor/autoload.php';
        @include_once $plugin_path . 'helper/tgeoip.php';

        if (!class_exists('TGeoIP'))
        {
            return;
        }
        
        // Check if database needs update. 
        $geo = new \TGeoIP();
        if (!$geo->needsUpdate())
        {
            return;
        }

        // Database is too old and needs an update! Let's inform user.
        return true;
    }
}