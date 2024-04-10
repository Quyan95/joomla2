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

$params = $box->params;

$form_labels = $params->get("mc_showlabels");

$form_labels_show  = (($form_labels=="0") || ($form_labels=="2")) ? true : false;
$form_placeholders = (($form_labels=="1") || ($form_labels=="2")) ? true : false;

$btn_style = array(
	"background-color:".$params->get("mc_submit_bg", "#5db75d"),
	"color:".$params->get("mc_submit_color", "#fff")
);

/* Prepare Fields Array */
$mail             = new stdclass;
$mail->name       = $params->get("mc_email_namefield");
$mail->type       = "email";
$mail->label      = $params->get("mc_email_name");
$mail->value      = null;
$mail->required   = true;
$mail->active     = true;

$field1           = new stdclass;
$field1->name     = $params->get("mc_merge1_name");
$field1->type     = $params->get("mc_merge1_type");
$field1->label    = $params->get("mc_merge1_label");
$field1->value    = $params->get("mc_merge1_value");
$field1->required = $params->get("mc_merge1_required");
$field1->active   = $params->get("mc_merge1_active");

$field2           = new stdclass;
$field2->name     = $params->get("mc_merge2_name");
$field2->type     = $params->get("mc_merge2_type");
$field2->label    = $params->get("mc_merge2_label");
$field2->value    = $params->get("mc_merge2_value");
$field2->required = $params->get("mc_merge2_required");
$field2->active   = $params->get("mc_merge2_active");	

$fields = array($mail, $field1, $field2);

$formname = "mcform-".$box->id;

if ($params->get("mc_submit_set_cookie", true))
{ 
	JFactory::getDocument()->addScriptDeclaration('
		document.addEventListener("DOMContentLoaded", function() {
			document.querySelector("#'. $formname .'").addEventListener("submit", function() {
				EngageBox.getInstance(' . $box->id . ').close();
			});
		});
	');
}

?>

<form action="<?php echo $params->get("mc_url"); ?>" class="eb" method="post" id="<?php echo $formname ?>" name="<?php echo $formname ?>" target="<?php echo $params->get("formtarget", "_self") ?>">
	<?php if ($params->get("mc_header", false)) { ?>
		<div class="eb-form-header"><?php echo $params->get("mc_header") ?></div>
	<?php } ?>

	<?php foreach ($fields as $field) { ?>
		<?php if ($field->active) { ?>
		<div class="eb-field-row">

			<?php if ($field->type == "checkbox") { ?>
				<input type="checkbox" name="<?php echo $field->name ?>" id="<?php echo $field->name ?>" value="<?php echo $field->value ?>" <?php echo ($field->required) ? "required" : "" ?>>

				<?php if (!$form_labels_show) { ?>
					<label for="<?php echo $field->name ?>"><?php echo $field->label ?></label>
				<?php } ?>
			<?php } ?>

			<?php if ($form_labels_show) { ?>
				<label for="<?php echo $field->name ?>"><?php echo $field->label ?></label>
			<?php } ?>

			<?php if ($field->type != "checkbox") { ?>

			<input class="eb-input" type="<?php echo $field->type ?>" name="<?php echo $field->name ?>" <?php if ($form_placeholders) { ?> placeholder="<?php echo $field->label ?>" <?php } ?> id="<?php echo $field->name ?>" value="<?php echo $field->value ?>" <?php echo ($field->required) ? "required" : "" ?>>

			<?php } ?>

		</div>
		<?php } ?>
	<?php } ?>
	
	<div class="eb-footer">
    	<button class="eb-btn" type="submit" name="subscribe" style="<?php echo implode(";", $btn_style) ?>">
    		<?php echo $box->params->get("mc_submit") ?>
    	</button>
    </div>
</form>