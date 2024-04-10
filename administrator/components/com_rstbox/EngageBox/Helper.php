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

use NRFramework\Cache;

class Helper 
{
    public static function getParams()
    {
        $hash = 'ebParams';

        if (Cache::has($hash))
        {
            return Cache::get($hash);
        }

        return Cache::set($hash, \JComponentHelper::getParams('com_rstbox'));
    }

    
    /**
     *  Returns all available box types
     *
     *  @return  array
     */
    public static function getBoxTypes()
    {
        \JPluginHelper::importPlugin('engagebox');
        \JFactory::getApplication()->triggerEvent('onEngageBoxTypes', array(&$types));

        asort($types);

        return $types;
    }
    

    /**
     *  Get Visitor ID
     *
     *  @return  string
     */
    public static function getVisitorID()
    {
        return \NRFramework\VisitorToken::getInstance()->get();
    }

    public static function arrayToCSSS($array)
    {
        $array = array_filter($array);

        if (empty($array))
        {
            return '';
        }

        $styles = '';

        foreach ($array as $key => $value)
        {
            $styles .= $key . ':' . $value . ';';
        }

        return $styles;
    }

    public static function licenseIsValid()
    {
        return \NRFramework\Functions::getDownloadKey();
    }

    
    /**
     * Checks if there are any EngageBox-related shortcodes available and replaces them.
     * 
     * @param   string  $text
     * 
     * @return  void
     */
    public static function replaceShortcodes(&$text)
    {
        // Check whether the plugin should process or not
        if (\Joomla\String\StringHelper::strpos($text, '{eb') === false)
        {
            return true;
        }

        // Search for this tag in the content
        $regex = "#{eb([^{}]++|\{(?1)\})+}#s";
        $text = preg_replace_callback($regex, [__CLASS__, 'processShortcode'], $text);
    }

    /**
     *  Callback to preg_replace_callback in the onContentPrepare event handler of this plugin. 
     * 
     *  Note: Unrecognized shortcodes will be skipped and won't be replaced. 
     *
     *  @param   array   $match  A match to any EngageBox-related shortcodes
     *
     *  @return  string  The processed result
     */
    private static function processShortcode($match)
    {
        if (!isset($match[0]))
        {
            return;
        }

        $originalShortcode = $match[0];

        // Find Shortcode
		$regex = 'eb([a-zA-Z]+)';
        preg_match_all('/' . $regex . '/is', 'eb' . $match[0], $params);
        if (!count($params[1]))
        {
            return $originalShortcode;
        }

        // Ensure shortcode exists
        $class = '\EngageBox\Shortcodes\\' . $params[1][0];
        if (!class_exists($class))
        {
            return $originalShortcode;
        }
        
        // Find options
        $regex = '(\w+)\s*=\s*(["\'])((?:(?!\2).)*)\2';
        preg_match_all('/' . $regex . '/is', $match[0], $params);
        if (!count($params[1]))
        {
            return $originalShortcode;
        }

        // Combine keys, values to create the shortcode options
        $opts = array_combine($params[1], $params[3]);
        
        // Render the shortcode
        return (new $class($opts))->render();
    }
    
}