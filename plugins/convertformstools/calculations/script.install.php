<?php

/**
 * @package         Convert Forms
 * @version         4.2.4 Pro
 * 
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2023 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

defined('_JEXEC') or die('Restricted access');

require_once __DIR__ . '/script.install.helper.php';

class PlgConvertFormsToolsCalculationsInstallerScript extends PlgConvertFormsToolsCalculationsInstallerScriptHelper
{
	public $name = 'calculations';
	public $alias = 'calculations';
	public $extension_type = 'plugin';
	public $plugin_folder = 'convertformstools';
	public $show_message = false;
}