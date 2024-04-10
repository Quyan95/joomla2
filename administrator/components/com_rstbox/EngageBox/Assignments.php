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

use EngageBox\Helper;
use EngageBox\Box;

/**
 *  EngageBox Assignments Class
 *  @todo - Refactor all local assignments to be using the framework's Condition class and let the framework run the checks.
 */
class Assignments
{
	/**
	 *  Item
	 *
	 *  @var  object
	 */
	private $box;

	/**
	 *  Local assignments list
	 *
	 *  @var  array
	 */
	private $assignments = array(
        
		'impressions',
        
		'cookietype',
        'offline'
    );

	/**
	 *  Class Constructor
	 *
	 *  @param  object  $item  The object to be checked
	 */
	public function __construct($box)
	{
        $this->box = $box;
	}

	/**
     *  Pass all checks
     *
     *  @return  boolean  Returns true if all assignments pass
     */
    public function passAll()
    {
        // Temporary fix for the Cookie assignment check as the cookie property doesn't have the 'assign_' prefix.
        // @todo - see class's comment.
        $this->box->params->set('assign_cookietype', true);

        $pass = true;

        foreach ($this->assignments as $key => $assignment)
        {
            // Break if not passed
            if (!$pass)
            {
                break;
            }
            
            $method = 'pass' . $assignment;

            // Skip item if there is no assosiated method
            if (!method_exists($this, $method))
            {
                continue;
            }

            $assign = 'assign_' . $assignment;

            // Skip item if assignment is missing
            if (!$this->box->params->exists($assign))
            {
                continue;
            }

            $pass = $this->$method();
        }

        return $pass;
    }

    /**
     *  Pass Check for Offline Mode
     *
     *  @return  bool
     */
    private function passOffline()
    {
        // Skip check if offline mode is disabled
        if (!\JFactory::getConfig()->get('offline', false))
        {
            return true;
        }

        $component   = Helper::getParams();
        $globalState = $component->get('assign_offline', true);
        $boxState    = $this->box->params->get('assign_offline', null);

        return is_null($boxState) ? $globalState : $boxState;
    }

    /**
     *  Pass Check for Box Cookie
     *
     *  @return  bool
     */
    private function passCookieType()
    {
        // Skip if assignment is disabled
        if ($this->box->params->get('cookietype') == 'never')
        {
            return true;
        }

        // Skip if box is on Test Mode and a Super User is logged in
        if ($this->box->testmode && \JFactory::getUser()->authorise('core.admin'))
        {
            return true;
        }

        $cookie = new Cookie($this->box->id);

        return !$cookie->exist();
    }

    
    /**
     *  Checks box maximum impressions assignment
     *
     *  @return  boolean  Returns true if the assignment passes
     */
    private function passImpressions()
    {
        // Skip if assignment is disabled
        if (!$this->box->params->get('assign_impressions', false))
        {
            return true;
        }

        $period = $this->box->params->get('assign_impressions_param_type', 'session');
        $limit  = (int) $this->box->params->get('assign_impressions_list');

        if ($limit == 0)
        {
            return;
        }

        $db = \JFactory::getDBO();
        $date = \JFactory::getDate();

        $query = $db->getQuery(true);

        $query
            ->select('COUNT(id)')
            ->from($db->quoteName('#__rstbox_logs'))
            ->where($db->quoteName('event') . ' = 1')
            ->where($db->quoteName('box') . ' = ' . $this->box->id);

        if ($period == "session")
        {
            $query->where($db->quoteName('sessionid') . ' = '. $db->quote(\JFactory::getSession()->getId()));
        } else
        {
            $query->where($db->quoteName('visitorid') . ' = '. $db->quote(Helper::getVisitorID()));
        }

        switch ($period)
        {
            case 'day':
                $query->where('DATE(date) = ' . $db->quote($date->format("Y-m-d")));
                break;
            case 'week':
                $query->where('YEARWEEK(date, 3) = ' . $date->format("oW"));
                break;
            case 'month':
                $query->where('MONTH(date) = ' . $date->format("m"));
                $query->where('YEAR(date) = ' . $date->format("Y"));
                break;
        }

        $db->setQuery($query);

        $pass = (int) $limit > (int) $db->loadResult();

        return (bool) $pass;
    }
    
}