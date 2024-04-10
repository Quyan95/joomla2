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
 
// import Joomla modelform library
jimport('joomla.application.component.modeladmin');
 
/**
 * Item Model
 */
class RstboxModelItem extends JModelAdmin
{
    /**
     * Returns a reference to the a Table object, always creating it.
     *
     * @param       type    The table type to instantiate
     * @param       string  A prefix for the table class name. Optional.
     * @param       array   Configuration array for model. Optional.
     * @return      JTable  A database object
     * @since       2.5
     */
    public function getTable($type = 'Items', $prefix = 'RstboxTable', $config = array()) 
    {
        return JTable::getInstance($type, $prefix, $config);
    }

    /**
     * Method to get the record form.
     *
     * @param       array   $data           Data for the form.
     * @param       boolean $loadData       True if the form is to load its own data (default case), false if not.
     * @return      mixed   A JForm object on success, false on failure
     * @since       2.5
     */
    public function getForm($data = array(), $loadData = true) 
    {
        // Get the form.
        $form = $this->loadForm('com_rstbox.item', 'item', array('control' => 'jform', 'load_data' => $loadData));

        if (empty($form)) 
        {
            return false;
        }

        return $form;
    }

    protected function preprocessForm(JForm $form, $data, $group = 'content')
    {
        $files = [
            'item_publishingassignments',
            'item_appearance',
            'item_trigger',
            'item_advanced'
        ];

        foreach ($files as $key => $value)
        {
            $form->loadFile($value, false);
        }

        $form->addFieldPath(JPATH_PLUGINS . '/system/nrframework/fields');

        JPluginHelper::importPlugin('engagebox');

        parent::preprocessForm($form, $data, $group);
    }

    public function getItem($pk = null)
    {
        if (!$item = parent::getItem($pk))
        {
            return;
        }

        $this->fixItemParams($item);
        return $item;
    }

    // What the fuck is this method doing?
    private function fixItemParams(&$item)
    {
        if (is_array($item->params) && count($item->params))
        {
            foreach ($item->params as $key => $value)
            {
                if (!isset($item->$key) && !is_object($value))
                {
                    $item->$key = $value;
                }
            }

            unset($item->params);
        }
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return    mixed    The data for the form.
     */
    protected function loadFormData()
    {
        $app = JFactory::getApplication();

        // Check the session for previously entered form data.
        $data = $app->getUserState('com_rstbox.edit.item.data', array());

        if (empty($data))
        {
            $data = $this->getItem();
        }

        // Load existing form template
        if ($template = $app->input->get('template', null))
        {
            if ($template = EngageBox\Library::getTemplate($template))
            {
                $this->fixItemParams($template);
                return $template;
            }
        }

        // When item is not saved yet, the $data variable is type of Array.
        $tempData = (object) $data;

        // In case the boxtype is missing default to 'custom'
        if (!isset($tempData->boxtype) || is_null($tempData->boxtype))
        {
            $tempData->boxtype = 'custom';
        }

        return $data;
    }

    /**
     * Method to save the form data.
     *
     * @param   array  The form data.
     *
     * @return  boolean  True on success.
     * @since   1.6
     */

    public function save($data)
    {
        $params = json_decode($data['params'], true);
        
        if (is_null($params))
        {
            $params = [];
        }

        switch ($params['display_conditions_type'])
        {
            // Validate Display Conditions payload
            case 'custom':
                NRFramework\Conditions\ConditionsHelper::getInstance()->onBeforeSave($params['rules']);
                break;

            // Make sure we have a valid mirror box
            case 'mirror':
                if (!isset($params['mirror_box']))
                {
                    $this->setError(JText::_('COM_ENGAGEBOX_ERROR_NO_MIRROR_BOX'));
                    return;
                }
        }

        $data['params'] = json_encode($params);

        if (is_array($data['position']))
        {
            $data['position'] = json_encode($data['position']);
        }
        
        return parent::save($data);
    }

    /**
     * Method to validate form data.
     */
    public function validate($form, $data, $group = null)
    {
        // Fix empty box title
        if (empty($data['name']))
        {
            $data['name'] = JText::_('COM_RSTBOX_UNTITLED_BOX');
        }

        // If the popup name exceeds 50 characters, save the first 50.
        if (strlen($data['name']) > 50)
        {
            $data['name'] = mb_substr($data['name'], 0, 50);
        }

        $newdata = array();
        $params  = array();
        $this->_db->setQuery('SHOW COLUMNS FROM #__rstbox');
        $dbkeys = $this->_db->loadObjectList('Field');
        $dbkeys = array_keys($dbkeys);

        foreach ($data as $key => $val)
        {
            if (in_array($key, $dbkeys))
            {
                $newdata[$key] = $val;
            }
            else
            {
                $params[$key] = $val;
            }
        }

        $newdata['params'] = json_encode($params);

        return $newdata;
    }

    /**
     * Method to copy an item
     *
     * @access    public
     * @return    boolean    True on success
     */
    public function copy($id)
    {
        $item = $this->getItem($id);

        // unset($item->_errors);
        $item->id = 0;
        $item->published = 0;

        // Keep 50 characters of name on export, otherwise, it wont save in DB due to column size restriction
        $item->name = mb_substr(JText::sprintf('NR_COPY_OF', $item->name), 0, 50);

        $item = $this->validate(null, get_object_vars($item));

        return ($this->save($item));
    }
}