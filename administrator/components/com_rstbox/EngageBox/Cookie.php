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

class Cookie
{
    /**
     * The Joomla Application Object
     *
     * @var object
     */
    private $app;

    /**
     * The box instance
     *
     * @var object
     */
    private $box;

    /**
     * The cookie request params.
     * 
     * @var object
     */
    private $cookie;

    /**
     * The value of the cookie
     *
     * @var int
     */
    private $cookie_value = 1;

    /**
     * The path of the cookie. It should be available in the whole domain.
     *
     * @var string
     */
    private $cookie_path = '/';

    /**
     * Class constructor
     *
     * @param integer $box_id   The box's ID
     */
    public function __construct($box_id)
    {
        $this->box = Box::get($box_id);
        $this->cookie = \JFactory::getApplication()->input->cookie;
    }

    /**
     * Get the name of the cookie
     *
     * @return string
     */
    private function getName()
    {   
        return isset($this->box->id) ? 'engagebox_' . $this->box->id : '';
    }
    
    /**
     * Store cookie in the browser 
     *
     * @return void
     */
    public function set()
    {
        if (!$this->box)
        {
            return;
        }

        $cookie_type = $this->box->params->get('cookietype', 'days');

        switch ($cookie_type)
        {
            case 'never':
                return;

            
            case 'days':
            case 'hours':
            case 'minutes':
                $expire = strtotime('now +' . (int) $this->box->cookie . ' ' . $cookie_type);
                break;
                
            case 'ever':
                $expire = strtotime('now +20 years'); // forever
                break;
            

            default:
                $expire = 0; // session
        }

        $this->cookie->set($this->getName(), $this->cookie_value, $expire, $this->cookie_path, '', true);
    }
  
    /**
     * Check if the cookie of the box exist
     *
     * @return bool
     */
    public function exist()
    {
        return $this->cookie->get($this->getName());
    }

    /**
     * Removes the cookie from the browser
     *
     * @return void
     */
    public function remove()
    {
        $this->cookie->set($this->getName(), $this->cookie_value, strtotime('-1 day'), $this->cookie_path);
    }
}