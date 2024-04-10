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

// Load Framework
if (!@include_once(JPATH_PLUGINS . '/system/nrframework/autoload.php'))
{
	throw new RuntimeException('Tassos Framework is not installed', 500);
}

// Initialize Convert Forms Library
require_once JPATH_ADMINISTRATOR . '/components/com_rstbox/autoload.php';

$app = JFactory::getApplication();

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_rstbox'))
{
	$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
	return;
}

use NRFramework\Functions;
use NRFramework\Extension;

// Load framework's and component's language files
Functions::loadLanguage();
Functions::loadLanguage('com_rstbox');
Functions::loadLanguage('plg_system_rstbox');

// Check required extensions
if (!Extension::pluginIsEnabled('nrframework'))
{
	$app->enqueueMessage(JText::sprintf('NR_EXTENSION_REQUIRED', JText::_('RSTBOX'), JText::_('PLG_SYSTEM_NRFRAMEWORK')), 'error');
}

if (!Extension::pluginIsEnabled('rstbox'))
{
	$app->enqueueMessage(JText::sprintf('NR_EXTENSION_REQUIRED', JText::_('RSTBOX'), JText::_('PLG_SYSTEM_RSTBOX')), 'error');
}

if (!Extension::componentIsEnabled('ajax'))
{
	$app->enqueueMessage(JText::sprintf('NR_EXTENSION_REQUIRED', JText::_('RSTBOX'), 'Ajax Interface'), 'error');
}

// Load component backend CSS
JHtml::stylesheet('com_rstbox/engagebox.sys.css', ['relative' => true, 'version' => 'auto']);

if (defined('nrJ4'))
{
	JHtml::stylesheet('plg_system_nrframework/joomla4.css', ['relative' => true, 'version' => 'auto']);
	JHtml::stylesheet('com_rstbox/joomla4.css', ['relative' => true, 'version' => 'auto']);
} else 
{
	JHtml::stylesheet('plg_system_nrframework/joomla3.css', ['relative' => true, 'version' => 'auto']);
	JHtml::stylesheet('com_rstbox/joomla3.css', ['relative' => true, 'version' => 'auto']);
}

// Perform the Request task
$controller = JControllerLegacy::getInstance('Rstbox');
$controller->execute($app->input->get('task'));
$controller->redirect();