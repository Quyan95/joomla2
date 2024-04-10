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
JFormHelper::loadFieldClass('modules');

class JFormFieldEBModules extends JFormFieldModules
{
    protected function getInput()
    {
		$modalName = 'modal_' . $this->id;

		JFactory::getDocument()->addScriptDeclaration('
			jQuery(function($) {
				$("#' . $modalName . '").on("shown.bs.modal", function() {
					var moduleID = $("#' . $this->id . '").val();
					var url = "' . JURI::base() . 'index.php?option=com_modules&view=module&task=module.edit&layout=modal&tmpl=component&id=" + moduleID;
					$("#' . $modalName . ' iframe").attr("src", url);
				})
			});
		');
		
		$options = [
			'title'       => JText::_('JLIB_HTML_EDIT_MODULE'),
			'url'         => '#',
			'height'      => '400px',
			'width'       => '800px',
			'backdrop'    => 'static',
			'bodyHeight'  => '70',
			'modalWidth'  => '70',
			'footer'      => '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal" aria-hidden="true">'
					. JText::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</button>                                      
					<button type="button" class="btn btn-primary" aria-hidden="true"
					<button type="button" class="btn btn-success" aria-hidden="true"
					onclick="jQuery(\'#' . $modalName . ' iframe\').contents().find(\'#applyBtn\').click();">'
					. JText::_('JAPPLY') . '</button>',
		];

		echo JHtml::_('bootstrap.renderModal', $modalName, $options);

		return parent::getInput() . 
			'<a class="btn btn-small btn-secondary editModule" data-bs-toggle="modal" data-toggle="modal" data-bs-target="#'. $modalName .'" href="#'. $modalName .'">
				<span class="icon-edit"></span> ' . JText::_('JLIB_HTML_EDIT_MODULE') . '
        	</a>';
    }
}