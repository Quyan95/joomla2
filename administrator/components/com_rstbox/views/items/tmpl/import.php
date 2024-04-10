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
defined('_JEXEC') or die;

JHtml::_('formbehavior.chosen', 'select');

?>
<form onsubmit="return submitform();" action="<?php echo JRoute::_('index.php?option=com_rstbox&view=items'); ?>" method="post" enctype="multipart/form-data" name="import-form" id="import-form">
	<fieldset class="form-horizontal">
		<legend><?php echo JText::_('NR_IMPORT_ITEMS'); ?></legend>
		<div class="control-group">
			<label for="file" class="control-label"><?php echo JText::_('COM_RSTBOX_CHOOSE_FILE'); ?></label>
			<div class="controls">
				<input class="input_box" id="file" name="file" type="file" size="57" />
			</div>
		</div>
		<div class="control-group">
			<label for="publish_all" class="control-label"><?php echo JText::_('NR_PUBLISH_ITEMS'); ?></label>
			<div class="controls">
				<fieldset id="publish_all" class="radio btn-group">
					<select name="publish_all">
						<option value="0"><?php echo JText::_('JNO'); ?></option>
						<option value="1"><?php echo JText::_('JYES'); ?></option>
						<option value="2" selected="selected"><?php echo JText::_('NR_AS_EXPORTED'); ?></option>
					</select>
				</fieldset>
			</div>
		</div>
		<div class="form-actions">
			<input class="btn btn-primary" type="submit" value="<?php echo JText::_('NR_IMPORT'); ?>" />
		</div>
	</fieldset>

	<input type="hidden" name="task" value="items.import" />
	<?php echo JHtml::_('form.token'); ?>
</form>

<script language="javascript" type="text/javascript">
	/**
	 * Submit the admin form
	 *
	 * small hack: let task decides where it comes
	 */
	function submitform() {
		var file = jQuery('#file').val();
		if (file) {
			var dot = file.lastIndexOf(".");
			if (dot != -1) {
				var ext = file.substr(dot, file.length);
				if ((ext == '.ebox') || (ext == '.rstbak')) {
					return true;
				}
			}
		}
		alert('<?php echo JText::_('NR_PLEASE_CHOOSE_A_VALID_FILE'); ?>');
		return false;
	}
</script>