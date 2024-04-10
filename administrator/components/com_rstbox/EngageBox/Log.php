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
use EngageBox\Helper;

class Log 
{
    /**
     *  Logs table
     *
     *  @var  string
     */
    private static $table = '#__rstbox_logs';
	
    /**
     *  Logs box events to the database
     *
     *  @param   integer  $boxid    The box id
     *  @param   integer  $eventid  Event id: 1=Open, 2=Close
     *
     *  @return  bool     Returns a boolean indicating if the event logged successfully
     */
    public static function track($boxid, $eventid = 1)
    {
    	// Making sure we have a valid Boxid
        if (!$boxid)
        {
            return;
        }

        // Get visitor's token id
        if (!$visitorID = Helper::getVisitorID())
        {
        	return;
        }

        // Everything seems OK. Let's save data to db.
        $data = (object) [
            'sessionid' => \JFactory::getSession()->getId(),
            'user'      => \JFactory::getUser()->id,
            'visitorid' => $visitorID,
            'box'       => $boxid,
            'event'     => $eventid,
            'date'      => \JFactory::getDate()->toSql()
        ];

        // Insert the object into the user profile table.
        try
        {
            \JFactory::getDbo()->insertObject(self::$table, $data);
            self::clean();
        } 
        catch (Exception $e)
        {
        }
    }

    /**
     *  Removes old rows from the logs table
     *  Runs every 12 hours with a self-check
     *
     *  @return void
     */
    private static function clean()
    {
        $hash = md5('eboxclean');

        if (Cache::read($hash, true))
        {
            return;
        }

        // Removes rows older than x days
        $days = Helper::getParams()->get('statsdays', 90);

        $db = \JFactory::getDbo();
         
        $query = $db->getQuery(true);
        $query
            ->delete($db->quoteName(self::$table))
            ->where($db->quoteName('date') . ' < DATE_SUB(NOW(), INTERVAL ' . $days . ' DAY)');
         
        $db->setQuery($query);
        $db->execute();

        // Write to cache file
        Cache::write($hash, 1, 720);

        return true;
    }
}