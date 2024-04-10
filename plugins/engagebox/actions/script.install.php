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

require_once __DIR__ . '/script.install.helper.php';

class PlgEngageBoxActionsInstallerScript extends PlgEngageBoxActionsInstallerScriptHelper
{
	public $name = 'PLG_ENGAGEBOX_ACTIONS';
	public $alias = 'actions';
	public $extension_type = 'plugin';
	public $plugin_folder = 'engagebox';
	public $show_message = false;
}
