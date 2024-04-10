<?php
/**
 * @package         EngageBox
 * @version         6.1.4 Pro
 *
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            http://www.tassos.gr
 * @copyright       Copyright © 2021 Tassos Marinos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Plugin\CMSPlugin;

class PlgButtonEngagebox extends CMSPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 */
	protected $autoloadLanguage = true;

    /**
     *  Application Object
     *
     *  @var  object
     */
    protected $app;

	/**
	 * EngageBox Button
	 *
	 * @param  string  $name  The name of the button to add
	 *
	 * @return JObject  The button object
	 */
	public function onDisplay($name)
	{
		JFactory::getDocument()->addStyleDeclaration('
			.ebox .icon-checkbox-partial, .mce-ico.icon-checkbox-partial {
			    color: #2a78cb;
			}
		');

		$component = $this->app->input->getCmd('option');
		$basePath  = $this->app->isClient('administrator') ? '' : 'administrator/';
		$link      = $basePath . 'index.php?option=com_rstbox&amp;view=rstbox&amp;layout=button&amp;tmpl=component&e_name=' . $name . '&e_comp='. $component;

		$button          = new JObject;
		$button->modal   = true;
		$button->class   = 'btn ebox';
		$button->link    = $link;
		$button->text    = JText::_('PLG_EDITORS-XTD_ENGAGEBOX_BUTTON_TEXT');
		$button->name    = 'checkbox-partial';

		if (defined('nrJ4'))
		{
			$button->options = [
				'height'     => '450px',
				'bodyHeight' => '450px',
				'modalWidth' => '230px',
			];
		} else 
		{
			$button->options = "{handler: 'iframe', size: {x: 350, y: 400}}";
		}

		return $button;
	}
}