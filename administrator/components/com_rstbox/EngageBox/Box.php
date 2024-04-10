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

use Joomla\Registry\Registry;
use NRFramework\Cache;
use NRFramework\Conditions\ConditionBuilder;

class Box
{
    public static function render($box)
    {
        \JPluginHelper::importPlugin('engagebox');

        // Check Publishing Assignments
        if (!self::pass($box))
        {
            return;
        }

        \JFactory::getApplication()->triggerEvent('onEngageBoxBeforeRender', [&$box]);

        self::prepare($box);

        $layout = new \JLayoutFile('box', null, ['debug' => false, 'client' => 1, 'component' => 'com_rstbox']);
        $html = $layout->render($box);

        \JFactory::getApplication()->triggerEvent('onEngageBoxAfterRender', [&$html, $box]);

        
        // Load the expression script only if an expression shortcode is found in the content
        if (strpos($html, '{ebExpr') !== false)
        {
            \JHtml::script('com_rstbox/expression.js', ['relative' => true, 'version' => 'auto']);
        }
        

        return $html;
    }

    private static function prepare(&$box)
    {
        $cParam = Helper::getParams();

        $css_class_prefix = 'eb-';

        $box->content = \JFactory::getApplication()->triggerEvent('onEngageBoxTypeRender', array($box));
        $box->content = implode(' ', $box->content);

        /* Classes */
        $css_class = [
            $box->boxtype
        ];

        $rtl = $box->params->get('rtl', '2') == '2' ? $cParam->get('rtl', false) : $box->params->get('rtl');
        $box->rtl = $rtl;
        if ($rtl)
        {
            $css_class[] = 'rtl';
        }

        self::prefixCSSClasses($css_class);

        // Add eb-{POPUP ID} manually as 0 (used in demos) is skipped via array_filter() in prefixCSSClasses()
        $css_class[] = 'eb-' . $box->id;

        $css_class[] = $box->params->get('classsuffix', '');

        $box->classes = $css_class;

        $dialog_css_classes = [
            
            $box->boxtype == 'emailform' ? 'form' . ucfirst($box->params->get('formorient', 'ver')) : null
            
        ];

        self::prefixCSSClasses($dialog_css_classes);
        $box->dialog_classes = $dialog_css_classes;

        // Apply the popup CSS
        $campaignCSS = new CSS($box);
        $campaignCSS->generate();

        $trigger_point_methods = [
            'pageready'    => 'onPageReady',
            'pageload'     => 'onPageLoad',
            'ondemand'     => 'onDemand',
            
            'pageheight'   => 'onScrollDepth',
            'element'      => 'onElementVisibility',
            'userleave'    => 'onExit',
            'onclick'      => 'onClick',
            'onexternallink' => 'onExternalLink',
            'elementHover' => 'onHover',
            
        ];

        /* Other Settings */
        // Use Namespaced classes for each trigger point and let them manipulate the settings dynamicaly.
        $box->settings = [
            'trigger'              => array_key_exists($box->triggermethod, $trigger_point_methods) ? $trigger_point_methods[$box->triggermethod] : $box->triggermethod,
            
            'trigger_selector'     => rtrim($box->params->get('triggerelement'), ','),
            'delay'                => $box->triggermethod == 'floatingbutton' ? 0 : (int) $box->params->get('triggerdelay'),
            //'scroll_dir'           => $box->params->get('scroll_dir', 'down'),
            'scroll_depth'         => $box->params->get('scroll_depth', 'percentage'),
            'scroll_depth_value'   => $box->params->get('scroll_depth', 'percentage') == 'percentage' ? (int) $box->params->get('triggerpercentage') : (int) $box->params->get('scroll_pixel'),
            'firing_frequency'     => (int) $box->params->get('firing_frequency', 1),
            'reverse_scroll_close' => (bool) $box->params->get('autohide'),
            'threshold'            => (float) $box->params->get('threshold', 0) / 100,
            'close_out_viewport'   => (bool) $box->params->get('close_out_viewport', false),
            'exit_timer'           => (int) $box->params->get('exittimer'),
            'idle_time'            => (int) $box->params->get('idle_time') * 1000,
            'prevent_default'      => (bool) $box->params->get('preventdefault', true),
            
            'animation_open'       => $box->params->get('animationin'),
            'animation_close'      => $box->params->get('animationout'),
            'animation_duration'   => (int) $box->params->get('duration'),
            
            
            'disable_page_scroll'  => (bool) ($box->params->get('preventpagescroll', '2') == '2' ? $cParam->get('preventpagescroll', false) : $box->params->get('preventpagescroll')),
            
            'test_mode'            => (bool) $box->testmode,
            'debug'                => (bool) $cParam->get('debug', false),
            
            'ga_tracking'          => (bool) $cParam->get('gaTrack', 0),
            'ga_tracking_id'       => $cParam->get('gaID', 0),
            'ga_tracking_event_category' => $cParam->get('gaCategory', 'EngageBox'),
            'ga_tracking_event_label' => $cParam->get('gaLabel', 'Box #{eb.id} - {eb.title}'),
            'auto_focus'           => (bool) $box->params->get('autofocus', false),
            
        ];

        $box->styles_container = [
            'z-index' => $box->params->get('zindex', 99999) != 99999 ? $box->params->get('zindex') : null
        ];

        $box->styles_container = Helper::arrayToCSSS($box->styles_container);

        // Let's start using CSS vars for each box settings.
        $cssVars = [
            'animation_duration' => (int) $box->params->get('duration') . 'ms'
        ];

        $cssVarsForm = self::cssVarsToString($cssVars, '.eb-' . $box->id);
        \JFactory::getDocument()->addStyleDeclaration($cssVarsForm);

        
        // Run Smart Tags replacements. For now is only required by the Google Analytics options.
        $box->settings = self::replaceSmartTags($box->settings, $box);
        

        
    }

    
    /**
     * Replace Smart Tags on subject
     *
     * @param   mixed  $subject
     * @param   object $box         The object instance
     * 
     * @return  mixed
     */
    public static function replaceSmartTags($subject, $box)
    {
        $tags = new \NRFramework\SmartTags();

        // Add box variables
        $box_tags = [
            'id'    => $box->id,
            'title' => $box->name
        ];

        $tags->add($box_tags, 'eb.');

        return $tags->replace($subject);
    }
    

    /**
     * Get a box object from the database
     *
     * @param  integer $id  The box's primary key
     *
     * @return object
     */
    public static function get($id)
    {
        $hash = md5('box_' . $id);

        if (Cache::has($hash))
        {
            return Cache::read($hash);
        }

        // Get a db connection.
        $db = \JFactory::getDbo();
        $query = $db->getQuery(true);
         
        $query->select('*')
            ->from($db->quoteName('#__rstbox'))
            ->where($db->quoteName('id') . ' = '. (int) $id);
         
        $db->setQuery($query);

        if (!$box = $db->loadObject())
        {
            return;
        }

        $box->params = new Registry($box->params);

        return Cache::set($hash, $box);
    }

    public static function isMirrored($id)
    {
        $boxes = Boxes::getAll();

        foreach ($boxes as $key => $box)
        {
            if (!isset($box->params->mirror) || !isset($box->params->mirror_box))
            {
                continue;
            }

            if ($box->params->mirror && $box->params->mirror_box == $id)
            {
                return true;
            }
        }

        return false;
    }

    public static function pass($box)
    {
        if (!$box)
        {
            return;
        }

        // Check first local assignments
        if (!self::passLocalAssignments($box))
        {
            return false;
        }

        $displayConditionsType = $box->params->get('display_conditions_type', '');

        // If empty, display popup sitewide
        if (empty($displayConditionsType))
        {
            return true;
        }

        // Mirror Display Conditions of another popup.
        if ($displayConditionsType == 'mirror' && $mirror_box_id = $box->params->get('mirror_box'))
        {
            $box->params->merge(self::getAssignmentsForMirroring($mirror_box_id));
        }

        // Get a recursive array of all rules
        $rules = json_decode(json_encode($box->params->get('rules', [])), true);

        // If testmode is enabled disable the User Groups assignment
        if ($box->testmode)
        {
            foreach ($rules as $key => &$group)
            {
                foreach ($group['rules'] as $_key => &$rule)
                {
                    if (!isset($rule['name']) || empty($rule['name']))
                    {
                        continue;
                    }

                    if ($rule['name'] === 'Joomla\UserGroup')
                    {
                        unset($group['rules'][$_key]);
                    }
                }
            }
        }

        // Check framework based assignments
        return ConditionBuilder::pass($rules);
    }

    /**
     * Check if a box passes local assignments
     *
     * @param [type] $box
     *
     * @return void
     */
    private static function passLocalAssignments($box)
    {
        $localAssignments = new \EngageBox\Assignments($box);
        return $localAssignments->passAll();
    }

    /**
     * Get assignments from mirroring box.
     * 
     * @param   int     $box_id
     * 
     * @return  object
     */
    private static function getAssignmentsForMirroring($box_id)
    {   
        // Load $box_id
        if (!$box = Box::get($box_id))
        {
            return;
        }

        return new Registry(['rules' => $box->params->get('rules')]);
    }

    public static function logOpenEvent($box_id)
    {
        $box = self::get($box_id);

        // Do not track if statistics option is disabled
        $track_open_event = (bool) (is_null($box->params->get('stats', null)) ? Helper::getParams()->get('stats', 1) : $box->params->get('stats'));
        if (!$track_open_event)
        {
            return;
        }

        Log::track($box_id);
    }

    private static function prefixCSSClasses(&$classes, $prefix = 'eb-')
    {
        $classes = array_filter($classes);

        foreach ($classes as &$class)
        {
            $class = $prefix . $class;
        }
    }

    private static function cssVarsToString($cssVars, $namespace)
    {
        $output = '';

        foreach ($cssVars as $key => $value)
        {
            $output .= '--' . $key . ': ' . $value . ';' . "\n";
        }

        return $namespace . ' {
                ' . $output . '
            }
        ';
    }

}