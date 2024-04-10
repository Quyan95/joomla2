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
extract($displayData);

?>

<div class="cf-checkbox-group">
	<input type="checkbox" name="<?php echo $field->input_name ?>" id="<?php echo $field->input_id; ?>"
		required
		value="1"
		class="<?php echo $field->class; ?>"
	>
	<label class="cf-label" for="<?php echo $field->input_id; ?>">
		<?php if (!empty($field->terms_url)) { ?>
			<a target="_blank" href="<?php echo $field->terms_url; ?>">
		<?php } ?>

		<?php echo $field->terms_text ?>

		<?php if (!empty($field->terms_url)) { ?>
			</a>
		<?php } ?>
	</label>
</div>