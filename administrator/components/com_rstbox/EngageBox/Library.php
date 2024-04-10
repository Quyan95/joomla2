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

use Joomla\CMS\Language\Text;

class Library extends \NRFramework\Library\Library
{
	public function __construct()
	{
		parent::__construct($this->getLibrarySettings());

		$this->init();
	}
	
	/**
	 * Returns the library settings.
	 * 
	 * @return  array
	 */
	private function getLibrarySettings()
	{
		$license_key = \NRFramework\Functions::getDownloadKey();

       	return [
			'id' => 'ebSelectTemplate',
			'title' => Text::_('COM_RSTBOX_LIBRARY'),
			'create_new_template_link' => \JURI::base() . 'index.php?option=com_rstbox&view=items&task=item.add',
			'main_category_label' => Text::_('COM_RSTBOX_POPUP_TYPE'),
			'product_license_settings_url' => $this->getNRFrameworkPluginURL(),
			'project' => 'engagebox',
			'project_version' => \NRFramework\Extension::getVersion('com_rstbox'),
			'project_name' => 'EngageBox',
			
			
			'project_license_type' => \NRFramework\Extension::isPro('com_rstbox') ? 'pro' : 'lite',
			'license_key' => $license_key,
			'license_key_status' => !empty($license_key) ? 'valid' : 'invalid',
			
			'blank_template_label' => Text::_('COM_RSTBOX_BLANK_POPUP'),
			'template_use_url' => \JURI::base() . 'index.php?option=com_rstbox&view=item&layout=edit&tf_use_template=1&template='
        ];
	}

	/**
	 * Returns the template.
	 * 
	 * @param   int    $id
	 * 
	 * @return  array
	 */
	public static function getTemplate($id)
	{
		if (!isset($_GET['tf_use_template']))
		{
			return;
		}
		
		// $template must be an integer
		$template = isset($_GET['template']) ? intval($_GET['template']) : '';
		if (empty($template) || $template === 'blank')
		{
			return;
		}
		
		$local_template_path = JPATH_ROOT . '/media/com_rstbox/templates/template.json';
		if (!file_exists($local_template_path))
		{
			return;
		}

		$local_template = file_get_contents($local_template_path);
		if (!$local_template = json_decode($local_template))
		{
			return;
		}

		if (!isset($local_template->id))
		{
			return;
		}

		if ((int) $local_template->id !== (int) $template)
		{
			return;
		}

		$local_template->params = json_decode($local_template->params, true);
		
		return $local_template;
	}
}