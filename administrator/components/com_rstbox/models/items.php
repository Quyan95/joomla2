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

use Joomla\String\StringHelper;

class RstboxModelItems extends JModelList
{
    /**
     * Constructor.
     *
     * @param    array    An optional associative array of configuration settings.
     *
     * @see        JController
     */
    public function __construct($config = array())
    {
        if (empty($config['filter_fields']))
        {
            $config['filter_fields'] = array(
                'ordering', 'a.ordering',
                'published', 'a.published',
                'state', 'a.state',
                'name', 'a.name',
                'search',
                'boxtype', 'a.boxtype',
                'triggermethod', 'a.triggermethod',
                'usergroups', 'devices',
                'impressions',
                'id', 'a.id'
            );
        }

        parent::__construct($config);
    }

    /**
     * Method to build an SQL query to load the list data.
     *
     * @return      string  An SQL query
     */
    protected function getListQuery()
    {
        // Create a new query object.           
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        // Select some fields from the item table
        $query
            ->select('a.id, a.name, a.testmode, a.boxtype, a.position, a.triggermethod, a.params, a.published')
            ->from('#__rstbox a');

        // Get box impressions
        $include_impressions = $this->getState('filter.impressions', true);
        if ($include_impressions)
        {
            $query->select('(SELECT count(id) from ' . $db->quoteName('#__rstbox_logs') . ' where box = a.id) as impressions');
        } else 
        {
            $query->select('0 as impressions');
        }
        
        // Filter State
        $filter = $this->getState('filter.state');
        if (is_numeric($filter))
        {
            $query->where($db->quoteName('a.published') . '= ' . ( int ) $filter);
        }
        else if ($filter == '')
        {
            $query->where($db->quoteName('a.published') . 'IN (0,1,2)');
        }

        // Filter Test Mode
        if ($testmode = $this->getState('filter.testmode'))
        {
            $query->where($db->quoteName('a.testmode') . ' = ' . $testmode);
        }

        // Filter Box Type
        $filter = $this->getState('filter.boxtype');
        if ($filter != '')
        {
            $query->where($db->quoteName('a.boxtype') . '=' . $db->q($filter));
        }

        // Exclude Boxes
        $excludeBoxes = (array) $this->getState('filter.exclude');
        if ($excludeBoxes)
        {
            $query->where($db->quoteName('a.id') . ' NOT IN (' . implode(',', $excludeBoxes) . ')');
        }

        // Filter the list over the search string if set.
        $search = $this->getState('filter.search');
        if (!empty($search))
        {
            if (stripos($search, 'id:') === 0)
            {
                $query->where($db->quoteName('a.id') . ' = ' . ( int ) substr($search, 3));
            }
            else
            {
                $search = $db->quote('%' . $db->escape($search, true) . '%');
                $query->where(
                    '('. $db->quoteName('a.name') . ' LIKE ' . $search . ' )'
                );
            }
        }

        // Filter Trigger Method
        $filter = $this->getState('filter.triggermethod');
        if ($filter != '')
        {
            $query->where($db->quoteName('a.triggermethod') . '=' . $db->q($filter));
        }  

        // Filter Assigned User Groups
        $filter = $this->getState('filter.usergroups');
        if ($filter != '')
        {
            $query->where($db->quoteName('a.params') . 'LIKE ' . $db->q('%"%usergroups%":["%' . $filter . '%"]%'));
        }

        // Filter Assigned Devices
        $filter = $this->getState('filter.devices');
        if ($filter != '')
        {
            $query->where($db->quoteName('a.params') . 'LIKE ' . $db->q('%"%devices%":["%' . $filter . '%"]%'));
        }

        // Add the list ordering clause.
        $orderCol  = $this->state->get('list.ordering', 'a.id');
        $orderDirn = $this->state->get('list.direction', 'desc');

        $query->order($db->escape($orderCol . ' ' . $orderDirn));

        return $query;
    }

    public function getItems()
    {
        if (!$items = parent::getItems())
        {
            return;
        }

        foreach ($items as $item)
        {
            $item->params = json_decode($item->params);

            // Prepare Cookie Type
            $cookieType = (isset($item->params->cookietype)) ? $item->params->cookietype : 'days';
            $item->params->cookietype = $cookieType;

            // Prepare usergroups
            if (
                !isset($item->params->assign_usergroups_list)
                || is_null($item->params->assign_usergroups_list)
                || (int) $item->params->assign_usergroups == 0)
            {         
                continue;
            }

            $usergroups = implode(",",$item->params->assign_usergroups_list);

            if (!$usergroups) 
            {
                continue;
            }

            $db = JFactory::getDBO();
            $query = $db->getQuery(true);

            $query
                ->select("*")
                ->from("#__usergroups")
                ->where("id in ($usergroups)");
    
            $db->setQuery($query);
            $usergroupsNames = $db->loadObjectList();

            $item->params->assign_usergroupsNames = $usergroupsNames;
        }

        return $items;
    }

    /**
     * Import Method
     * Import the selected items specified by id
     * and set Redirection to the list of items
     */
    function import($model)
    {
		// We don't use the Joomla! Framework here to get the uploaded file due to a bug with the JInput Class
		// which is unable to detect some files downloaded from Google Drive.
        $file = $_FILES['file'];
        
        $app = JFactory::getApplication();

        if (!is_array($file) || !isset($file['name']))
        {
            $app->enqueueMessage(JText::_('NR_PLEASE_CHOOSE_A_VALID_FILE'));
            $app->redirect('index.php?option=com_rstbox&view=items&layout=import');
        }

        $ext = explode(".", $file['name']);

        if (!in_array($ext[count($ext) - 1], array("ebox","rstbak")))
        {
            $app->enqueueMessage(JText::_('NR_PLEASE_CHOOSE_A_VALID_FILE'));
            $app->redirect('index.php?option=com_rstbox&view=items&layout=import');
        }

        jimport('joomla.filesystem.file');
        $publish_all = $app->input->getInt('publish_all', 0);

        $data = file_get_contents($file['tmp_name']);

        if (empty($data))
        {
            $app->enqueueMessage(JText::_('File is empty!'));
            $app->redirect('index.php?option=com_rstbox&view=items');
            return;
        }
        
        $items = json_decode($data, true);
        if (is_null($items))
        {
            $items = array();
        }

        $msg = JText::_('Items saved');

        foreach ($items as $item)
        {
            $item['id'] = 0;
            
            if (in_array($publish_all, [0, 1]))
            {
                $item['published'] = $publish_all;
            }

            $items[] = $item;

            $saved = $model->save($item);

            if ($saved != 1)
            {
                $msg = JText::_('Error Saving Item') . ' ( ' . $saved . ' )';
            }
        }

        $app->enqueueMessage($msg);
        $app->redirect('index.php?option=com_rstbox&view=items');
    }

    /**
     * Export Method
     * Export the selected items specified by id
     */
    function export($ids)
    {
        $db    = $this->getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__rstbox')
            ->where('id IN ( ' . implode(', ', $ids) . ' )');
        $db->setQuery($query);
        $rows = $db->loadObjectList();

        $string = json_encode($rows);

        $filename = JText::_("COM_RSTBOX") . ' Items';
        if (count($rows) == 1)
        {
            $name = StringHelper::strtolower(html_entity_decode($rows['0']->name));
            $name = preg_replace('#[^a-z0-9_-]#', '_', $name);
            $name = trim(preg_replace('#__+#', '_', $name), '_-');

            $filename = JText::_("COM_RSTBOX") .  ' Item (' . $name . ')';
        }

        // SET DOCUMENT HEADER
        if (preg_match('#Opera(/| )([0-9].[0-9]{1,2})#', $_SERVER['HTTP_USER_AGENT']))
        {
            $UserBrowser = "Opera";
        }
        elseif (preg_match('#MSIE ([0-9].[0-9]{1,2})#', $_SERVER['HTTP_USER_AGENT']))
        {
            $UserBrowser = "IE";
        }
        else
        {
            $UserBrowser = '';
        }
        $mime_type = ($UserBrowser == 'IE' || $UserBrowser == 'Opera') ? 'application/octetstream' : 'application/octet-stream';
        @ob_end_clean();
        ob_start();

        header('Content-Type: ' . $mime_type);
        header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');

        if ($UserBrowser == 'IE')
        {
            header('Content-Disposition: inline; filename="' . $filename . '.ebox"');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
        }
        else
        {
            header('Content-Disposition: attachment; filename="' . $filename . '.ebox"');
            header('Pragma: no-cache');
        }

        // PRINT STRING
        echo $string;
        die;
    }

    /**
     * Copy Method
     * Copy all items specified by array cid
     * and set Redirection to the list of items
     */
    function copy($ids, $model)
    {
        foreach ($ids as $id)
        {
            $model->copy($id);
        }

        JFactory::getApplication()->enqueueMessage(JText::sprintf('Items copied', count($ids)));
        JFactory::getApplication()->redirect('index.php?option=com_rstbox&view=items');
    }

    /**
     *  Resets box statistics
     *
     *  @return  void
     */
    function reset($ids)
    {
        $db = JFactory::getDbo();
         
        $query = $db->getQuery(true);
         
        $conditions = array(
            $db->quoteName('box') . ' IN ('.implode(",", $ids).')'
        );
         
        $query->delete($db->quoteName('#__rstbox_logs'));
        $query->where($conditions);
         
        $db->setQuery($query);
        $db->execute();

        JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_RSTBOX_N_ITEMS_RESET_1', count($ids)));
        JFactory::getApplication()->redirect('index.php?option=com_rstbox&view=items');
    }
}