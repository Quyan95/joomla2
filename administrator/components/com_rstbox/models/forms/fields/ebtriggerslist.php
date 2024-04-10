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

defined('_JEXEC') or die('Restricted access');

JFormHelper::loadFieldClass('list');

class JFormFieldEBTriggersList extends JFormFieldList
{
    /**
     * Method to get a list of options for a list input.
     *
     * @return    array   An array of JHtml options.
     */
    protected function getOptions()
    {
        $triggers = [
            'pageload'        => 'COM_RSTBOX_ITEM_TRIGGER_PAGELOAD',
            'pageready'       => 'COM_RSTBOX_ITEM_TRIGGER_PAGEREADY',
            'onclick'         => 'COM_RSTBOX_ITEM_TRIGGER_ONCLICK',
            'ondemand'        => 'COM_RSTBOX_ITEM_TRIGGER_ONDEMAND',
            
            'pageheight'      => 'COM_RSTBOX_ITEM_TRIGGER_PAGEHEIGHT',
            'element'         => 'COM_RSTBOX_ITEM_TRIGGER_ELEMENT',
            'userleave'       => 'COM_RSTBOX_ITEM_TRIGGER_USERLEAVE',
            'elementHover'    => 'COM_RSTBOX_ITEM_TRIGGER_ELEMENTHOVER',
            'onAdBlockDetect' => 'COM_RSTBOX_ITEM_TRIGGER_ONADBLOCKDETECT',
            'onIdle'          => 'COM_RSTBOX_ITEM_TRIGGER_ONIDLE',
            'onexternallink'  => 'COM_RSTBOX_ITEM_TRIGGER_ONEXTERNALLINK',
            'floatingbutton'  => 'COM_RSTBOX_ITEM_TRIGGER_FLOATINGBUTTON'
            
        ];

        foreach ($triggers as $key => $title)
        {
            $options[] = JHTML::_('select.option', $key, JText::_($title));
        }   

        return array_merge(parent::getOptions(), $options);
    }
}