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

defined('_JEXEC') or die;

JHtml::script('com_rstbox/editorbutton.js', ['relative' => true, 'version' => 'auto']);

JFactory::getDocument()->addStyleDeclaration('
	.eboxEditorButton {
	    padding: 25px;
	}
');

?>
<div class="eboxEditorButton">
	<form>
		<?php echo $this->form->renderFieldset("main") ?>
		<button onclick="insertEngageBoxhortcode('<?php echo $this->eName; ?>', <?php echo defined('nrJ4') ? 'true' : 'false' ?>);" class="btn btn-success span12">
			<?php echo JText::_('PLG_EDITORS-XTD_ENGAGEBOX_INSERTBUTTON'); ?>
		</button>
	</form>
</div>