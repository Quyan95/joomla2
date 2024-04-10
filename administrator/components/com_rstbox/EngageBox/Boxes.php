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

use EngageBox\Box;
use EngageBox\Helper;
use NRFramework\Cache;
use Joomla\Registry\Registry;

class Boxes
{
    public static function render()
    {
        $db = \JFactory::getDbo();

        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__rstbox'))
            ->where($db->quoteName('published') . ' = 1');

        // If visitor is not logged-in as a Super User, get non-test-mode boxes only.
        if (!\JFactory::getUser()->authorise('core.admin'))
        { 
            $query->where($db->quoteName('testmode') . ' = 0');
        }
         
        $db->setQuery($query);
        
        if (!$ids = $db->loadColumn())
        {
            return;
        }

        $html = '';

        foreach ($ids as $id)
        {
            // Get box object
            $box = Box::get($id);

            // Render box
            $html .= Box::render($box);
        }

        
        if (!empty($html) && Helper::getParams()->get('preparecontent', true))
        {
            $html = \JHtml::_('content.prepare', $html);
        }
        

        return $html;
    }

    /**
     * Get all boxes regardless their published state
     *
     * @return array
     */
    public static function getAll()
    {
        $hash = 'boxes';

        if (Cache::has($hash))
        {
            return Cache::read($hash);
        }

        \JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_rstbox/' . 'models');
        \JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_rstbox/' . 'tables');
        
        $model = \JModelLegacy::getInstance('Items', 'RstboxModel', ['ignore_request' => true]);
        $result = $model->getItems();

        return Cache::set($hash, $result);
    }
}