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

namespace EngageBox\Shortcodes;

defined('_JEXEC') or die('Restricted access');

abstract class Shortcode
{
	/**
	 * Shortcode options.
	 * 
	 * @var  string
	 */
	private $opts = [];
	
	public function __construct($opts = [])
	{
		$this->opts = $opts;
	}

	/**
	 * Renders the shortcode.
	 * 
	 * @return  string
	 */
	public function render()
	{
		return \NRFramework\Widgets\Helper::render($this->name, $this->opts);
	}
}