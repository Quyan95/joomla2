<?php
/**
 * @package         Regular Labs Library
 * @version         23.10.25560
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            https://regularlabs.com
 * @copyright       Copyright © 2023 Regular Labs All Rights Reserved
 * @license         GNU General Public License version 2 or later
 */

namespace RegularLabs\Library;

defined('_JEXEC') or die;

use Joomla\CMS\Client\ClientHelper as JClientHelper;
use Joomla\CMS\Client\FtpClient as JFtpClient;
use Joomla\CMS\Factory as JFactory;
use Joomla\CMS\Filesystem\Folder as JFolder;
use Joomla\CMS\Filesystem\Path as JPath;
use Joomla\CMS\Language\Text as JText;
use Joomla\CMS\Log\Log as JLog;
use Joomla\CMS\Uri\Uri as JUri;

class File
{
    static $is_external = [];

    /**
     * some/url/to/a/file.ext
     * > some/url/to/a/file_suffix.ext
     */
    public static function addSuffix($url, $suffix)
    {
        $url = StringHelper::normalize($url);

        $info = pathinfo($url);

        return ($info['dirname'] ?? '')
            . '/' . ($info['filename'] ?? '')
            . $suffix
            . '.' . ($info['extension'] ?? '');
    }

    /**
     * Delete a file or array of files
     *
     * @param mixed   $file               The file name or an array of file names
     * @param boolean $show_messages      Whether or not to show error messages
     * @param int     $min_age_in_minutes Minimum last modified age in minutes
     *
     * @return  boolean  True on success
     *
     * @since   11.1
     */
    public static function delete($file, $show_messages = false, $min_age_in_minutes = 0)
    {
        $FTPOptions = JClientHelper::getCredentials('ftp');
        $pathObject = new JPath;

        $files = is_array($file) ? $file : [$file];

        if ($FTPOptions['enabled'] == 1)
        {
            // Connect the FTP client
            $ftp = JFtpClient::getInstance($FTPOptions['host'], $FTPOptions['port'], [], $FTPOptions['user'], $FTPOptions['pass']);
        }

        foreach ($files as $file)
        {
            $file = $pathObject->clean($file);

            if ( ! is_file($file))
            {
                continue;
            }

            if ($min_age_in_minutes && floor((time() - filemtime($file)) / 60) < $min_age_in_minutes)
            {
                continue;
            }

            // Try making the file writable first. If it's read-only, it can't be deleted
            // on Windows, even if the parent folder is writable
            @chmod($file, 0777);

            if ($FTPOptions['enabled'] == 1)
            {
                $file = $pathObject->clean(str_replace(JPATH_ROOT, $FTPOptions['root'], $file), '/');

                if ( ! $ftp->delete($file))
                {
                    // FTP connector throws an error
                    return false;
                }
            }

            // Try the unlink twice in case something was blocking it on first try
            if ( ! @unlink($file) && ! @unlink($file))
            {
                $show_messages && JLog::add(JText::sprintf('JLIB_FILESYSTEM_DELETE_FAILED', basename($file)), JLog::WARNING, 'jerror');

                return false;
            }
        }

        return true;
    }

    /**
     * Delete a folder.
     *
     * @param string  $path               The path to the folder to delete.
     * @param boolean $show_messages      Whether or not to show error messages
     * @param int     $min_age_in_minutes Minimum last modified age in minutes
     *
     * @return  boolean  True on success.
     */
    public static function deleteFolder($path, $show_messages = false, $min_age_in_minutes = 0)
    {
        @set_time_limit(ini_get('max_execution_time'));
        $pathObject = new JPath;

        if ( ! $path)
        {
            $show_messages && JLog::add(__METHOD__ . ': ' . JText::_('JLIB_FILESYSTEM_ERROR_DELETE_BASE_DIRECTORY'), JLog::WARNING, 'jerror');

            return false;
        }

        // Check to make sure the path valid and clean
        $path = $pathObject->clean($path);

        if ( ! is_dir($path))
        {
            $show_messages && JLog::add(JText::sprintf('JLIB_FILESYSTEM_ERROR_PATH_IS_NOT_A_FOLDER', $path), JLog::WARNING, 'jerror');

            return false;
        }

        // Remove all the files in folder if they exist; disable all filtering
        $files = JFolder::files($path, '.', false, true, [], []);

        if ( ! empty($files))
        {
            if (self::delete($files, $show_messages, $min_age_in_minutes) !== true)
            {
                // JFile::delete throws an error
                return false;
            }
        }

        // Remove sub-folders of folder; disable all filtering
        $folders = JFolder::folders($path, '.', false, true, [], []);

        foreach ($folders as $folder)
        {
            if (is_link($folder))
            {
                // Don't descend into linked directories, just delete the link.

                if (self::delete($folder, $show_messages, $min_age_in_minutes) !== true)
                {
                    return false;
                }

                continue;
            }

            if ( ! self::deleteFolder($folder, $show_messages, $min_age_in_minutes))
            {
                return false;
            }
        }

        // Skip if folder is not empty yet
        if ( ! empty(JFolder::files($path, '.', false, true, [], []))
            || ! empty(JFolder::folders($path, '.', false, true, [], []))
        )
        {
            return true;
        }

        if (@rmdir($path))
        {
            return true;
        }

        $FTPOptions = JClientHelper::getCredentials('ftp');

        if ($FTPOptions['enabled'] == 1)
        {
            // Connect the FTP client
            $ftp = JFtpClient::getInstance($FTPOptions['host'], $FTPOptions['port'], [], $FTPOptions['user'], $FTPOptions['pass']);

            // Translate path and delete
            $path = $pathObject->clean(str_replace(JPATH_ROOT, $FTPOptions['root'], $path), '/');

            // FTP connector throws an error
            return $ftp->delete($path);
        }

        if ( ! @rmdir($path))
        {
            $show_messages && JLog::add(JText::sprintf('JLIB_FILESYSTEM_ERROR_FOLDER_DELETE', $path), JLog::WARNING, 'jerror');

            return false;
        }

        return true;
    }

    /**
     * some/url/to/a/file.ext
     * > file.ext
     */
    public static function getBaseName($url, $lowercase = false)
    {
        $url = StringHelper::normalize($url);

        $basename = ltrim(basename($url), '/');

        $parts = explode('?', $basename);

        $basename = $parts[0];

        if ($lowercase)
        {
            $basename = strtolower($basename);
        }

        return $basename;
    }

    /**
     * some/url/to/a/file.ext
     * > some/url/to/a
     */
    public static function getDirName($url)
    {
        $url = StringHelper::normalize($url);
        $url = strtok($url, '?');
        $url = strtok($url, '#');

        return rtrim(dirname($url), '/');
    }

    /**
     * some/url/to/a/file.ext
     * > ext
     */
    public static function getExtension($url)
    {
        $info = pathinfo($url);

        if ( ! isset($info['extension']))
        {
            return '';
        }

        $ext = explode('?', $info['extension']);

        return strtolower($ext[0]);
    }

    /**
     * some/url/to/a/file.ext
     * > file
     */
    public static function getFileName($url, $lowercase = false)
    {
        $url = StringHelper::normalize($url);

        $info = @pathinfo($url);

        $filename = $info['filename'] ?? $url;

        if ($lowercase)
        {
            $filename = strtolower($filename);
        }

        return $filename;
    }

    public static function getFileTypes($type = 'images')
    {
        return match ($type)
        {
            'image', 'images'       => [
                'bmp',
                'flif',
                'gif',
                'jpe',
                'jpeg',
                'jpg',
                'png',
                'tiff',
                'eps',
            ],
            'audio'                 => [
                'aif',
                'aiff',
                'mp3',
                'wav',
            ],
            'video', 'videos'       => [
                '3g2',
                '3gp',
                'avi',
                'divx',
                'f4v',
                'flv',
                'm4v',
                'mov',
                'mp4',
                'mpe',
                'mpeg',
                'mpg',
                'ogv',
                'swf',
                'webm',
                'wmv',
            ],
            'document', 'documents' => [
                'doc',
                'docm',
                'docx',
                'dotm',
                'dotx',
                'odb',
                'odc',
                'odf',
                'odg',
                'odi',
                'odm',
                'odp',
                'ods',
                'odt',
                'onepkg',
                'onetmp',
                'onetoc',
                'onetoc2',
                'otg',
                'oth',
                'otp',
                'ots',
                'ott',
                'oxt',
                'pdf',
                'potm',
                'potx',
                'ppam',
                'pps',
                'ppsm',
                'ppsx',
                'ppt',
                'pptm',
                'pptx',
                'rtf',
                'sldm',
                'sldx',
                'thmx',
                'xla',
                'xlam',
                'xlc',
                'xld',
                'xll',
                'xlm',
                'xls',
                'xlsb',
                'xlsm',
                'xlsx',
                'xlt',
                'xltm',
                'xltx',
                'xlw',
            ],
            'other', 'others'       => [
                'css',
                'csv',
                'js',
                'json',
                'tar',
                'txt',
                'xml',
                'zip',
            ],
            default                 => [
                ...self::getFileTypes('images'),
                ...self::getFileTypes('audio'),
                ...self::getFileTypes('videos'),
                ...self::getFileTypes('documents'),
                ...self::getFileTypes('other'),
            ]
        };
    }

    /**
     * Find a matching media file in the different possible extension media folders for given type
     *
     * @param string $type (css/js/...)
     * @param string $file
     *
     * @return bool|string
     */
    public static function getMediaFile($type, $file)
    {
        // If http is present in filename
        if (str_starts_with($file, 'http') || str_starts_with($file, '//'))
        {
            return $file;
        }

        $files = [];

        // Detect debug mode
        if (JFactory::getApplication()->get('debug') || JFactory::getApplication()->input->get('debug'))
        {
            $files[] = str_replace(['.min.', '-min.'], '.', $file);
        }

        $files[] = $file;

        /**
         * Loop on 1 or 2 files and break on first find.
         * Add the content of the MD5SUM file located in the same folder to url to ensure cache browser refresh
         * This MD5SUM file must represent the signature of the folder content
         */
        foreach ($files as $check_file)
        {
            $file_found = self::findMediaFileByFile($check_file, $type);

            if ( ! $file_found)
            {
                continue;
            }

            return $file_found;
        }

        return false;
    }

    public static function isDocument($url)
    {
        return self::isMedia($url, self::getFileTypes('documents'));
    }

    public static function isExternal($url)
    {
        if (isset(static::$is_external[$url]))
        {
            return static::$is_external[$url];
        }

        $uri = parse_url($url);

        if (empty($uri['host']))
        {
            static::$is_external[$url] = false;

            return static::$is_external[$url];
        }

        // give preference to SERVER_NAME, because this includes subdomains
        $hostname = $_SERVER['SERVER_NAME'] ?: $_SERVER['HTTP_HOST'];

        static::$is_external[$url] = ! (strcasecmp($hostname, $uri['host']) === 0);

        return static::$is_external[$url];
    }

    public static function isExternalVideo($url)
    {
        return (str_contains($url, 'youtu.be')
            || str_contains($url, 'youtube.com')
            || str_contains($url, 'vimeo.com')
        );
    }

    public static function isImage($url)
    {
        return self::isMedia($url, self::getFileTypes('images'));
    }

    public static function isInternal($url)
    {
        return ! self::isExternal($url);
    }

    public static function isMedia($url, $filetypes = [])
    {
        $filetype = self::getExtension($url);

        if ( ! $filetype)
        {
            return false;
        }

        if ( ! is_array($filetypes))
        {
            $filetypes = [$filetypes];
        }

        if (count($filetypes) == 1 && str_contains($filetypes[0], ','))
        {
            $filetypes = ArrayHelper::toArray($filetypes[0]);
        }

        $filetypes = ($filetypes ?? null) ?: self::getFileTypes();

        return in_array($filetype, $filetypes);
    }

    public static function isVideo($url)
    {
        return self::isMedia($url, self::getFileTypes('videos'));
    }

    public static function trimFolder($folder)
    {
        return trim(str_replace(['\\', '//'], '/', $folder), '/');
    }

    /**
     * Find a matching media file in the different possible extension media folders for given type
     *
     * @param string $file
     * @param string $type (css/js/...)
     *
     * @return bool|string
     */
    private static function findMediaFileByFile($file, $type)
    {
        $template = JFactory::getApplication()->getTemplate();

        // If the file is in the template folder
        $file_found = self::getFileUrl('/templates/' . $template . '/' . $type . '/' . $file);

        if ($file_found)
        {
            return $file_found;
        }

        // Try to deal with system files in the media folder
        if ( ! str_contains($file, '/'))
        {
            $file_found = self::getFileUrl('/media/system/' . $type . '/' . $file);

            if ( ! $file_found)
            {
                return false;
            }

            return $file_found;
        }

        $paths = [];

        // If the file contains any /: it can be in a media extension subfolder
        // Divide the file extracting the extension as the first part before /
        [$extension, $file] = explode('/', $file, 2);

        $paths[] = '/media/' . $extension . '/' . $type;
        $paths[] = '/templates/' . $template . '/' . $type . '/system';
        $paths[] = '/media/system/' . $type;
        $paths[] = '';

        foreach ($paths as $path)
        {
            $file_found = self::getFileUrl($path . '/' . $file);

            if ( ! $file_found)
            {
                continue;
            }

            return $file_found;
        }

        return false;
    }

    /**
     * Get the url for the file
     *
     * @param string $path
     *
     * @return bool|string
     */
    private static function getFileUrl($path)
    {
        if ( ! file_exists(JPATH_ROOT . $path))
        {
            return false;
        }

        return JUri::root(true) . $path;
    }
}
