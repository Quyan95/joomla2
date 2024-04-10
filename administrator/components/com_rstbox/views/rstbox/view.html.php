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

// import Joomla view library
jimport('joomla.application.component.view');

class RstboxViewRstbox extends JViewLegacy
{
	/**
	 * Items view display method
	 * @return void
	 */
	function display($tpl = null)
	{
		if ($this->getLayout() == 'button')
		{
			// Load plugin language file
			NRFramework\Functions::loadLanguage("plg_editors-xtd_engagebox");

			// Get editor name
			$eName = JFactory::getApplication()->input->getCmd('e_name');
			
			// Get form fields
			$xml = JPATH_PLUGINS . "/editors-xtd/engagebox/form.xml";
	        $form = new JForm("com_rstbox.button", array('control' => 'jform'));
	        $form->loadFile($xml, false);

	        // Remove "USESAMEBOX" option if we are not editing a box
	        if (JFactory::getApplication()->input->getCmd('e_comp') != "com_rstbox")
			{
	        	unset($form->getXml()->fieldset->field[0]->option);
			}

	        // Template properties
	        $this->eName = preg_replace('#[^A-Z0-9\-\_\[\]]#i', '', $eName);
            $this->form = $form;

			parent::display($tpl);
			return;
		}

		$this->config = EngageBox\Helper::getParams();

		JHtml::stylesheet('jui/icomoon.css', array(), true);

		// Set the toolbar
		$this->addToolBar();

		// Display the template
		parent::display($tpl);
	}

	/**
	 *  Add Toolbar to layout
	 */
	protected function addToolBar()
	{
		JToolBarHelper::title(JText::_('RSTBOX'));

		$canDo      = EngageBox\Admin::getActions();
		$state      = $this->get('State');
		$viewLayout = JFactory::getApplication()->input->get('layout', 'default');

		if ($canDo->get('core.admin'))
		{
			JToolbarHelper::preferences('com_rstbox');
		}

		JToolbarHelper::help("Help", false, 'https://www.tassos.gr/joomla-extensions/engagebox/docs');
	}
}