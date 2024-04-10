<?php

/**
 * @package         EngageBox
 * @version         6.1.4 Pro
 * 
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            http://www.tassos.gr
 * @copyright       Copyright © 2019 Tassos Marinos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

defined('_JEXEC') or die('Restricted access');

JFormHelper::loadFieldClass('list');

use NRFramework\Functions;

class JFormFieldBoxes extends JFormFieldList
{
    /**
     * The box list
     *
     * @var object
     */
    private $boxes;

    private $exclude_editing_box;

    /**
     * Render the input field
     *
     * @return void
     */
    protected function getInput()
    {
        $this->exclude_editing_box = $this->element['excludeeditingbox'] == 'true';

        if (!$this->boxes = $this->getBoxes())
        {
            Functions::loadLanguage('com_rstbox');

            return JText::_('COM_RSTBOX_NO_BOXES_FOUND');
        }

        return parent::getInput();
    }

    /**
     * Method to get a list of options for a list input.
     *
     * @return    array   An array of JHtml options.
     */
    protected function getOptions()
    {
        if (!$this->boxes)
        {
            return;
        }

        $options = [];

        foreach ($this->boxes as $box)
        {
            // Exclude active editing box
            if ($this->getEditingBoxID() == $box->id)
            {
                array_unshift($options, JHTML::_('select.option', $box->id, JText::_('COM_ENGAGEBOX_THIS_BOX')));
                continue;
            }

            $option = JHTML::_('select.option', $box->id, $box->name . ' (' . $box->id . ')');
            $options[] = $option;
        }   

        return array_merge(parent::getOptions(), $options);
    }

    /**
     * Get list of boxes
     *
     * @return void
     */
    private function getBoxes()
    {
        JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_rstbox/' . 'models');

        $model = JModelLegacy::getInstance('Items', 'RstboxModel', array('ignore_request' => true));
        $model->setState('filter.state', 1);
        $model->setState('filter.impressions', false);

        // Exclude active editing box
        if ($this->exclude_editing_box && $current_box = $this->getEditingBoxID())
        {
            $model->setState('filter.exclude', $current_box);
        }

        return $model->getItems();
    }

    private function getEditingBoxID()
    {
        $input = JFactory::getApplication()->input;

        /**
         * First check the keys `request_option` and `request_layout` respectively instead of `option` and `layout`
         * in case an AJAX request needs to sends us these data but cannot use the `option` and `layout` as different
         * values are needed for the AJAX request to function properly.
         * 
         * i.e. ConditionBuilder's "Viewed Another Box" should not display the current box when fetching the rule fields
         * via AJAX. Since its not possible to get the current box ID after the AJAX has happened, we send the `id`
         * (popup ID) alongside the `request_option` (com_rstbox) and `request_layout` (edit) parameters.
         */
        $option = $input->get('request_option') ? $input->get('request_option') : $input->get('option');
        $layout = $input->get('request_layout') ? $input->get('request_layout') : $input->get('layout');

        if ($option == 'com_rstbox' && $layout == 'edit')
        {
            return $input->getInt('id');
        }

        return false;
    }
}