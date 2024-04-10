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

// Reset all instances of the Signature field within all visible Convert Forms
ConvertForms\Helper::addScriptDeclarationOnce('
	ConvertForms.Helper.onReady(function() {
		let forms = document.querySelectorAll(".convertforms");
		if (forms) {
			forms.forEach(function(form) {
				// Reset on Convert Forms successful submission
				form.addEventListener("success", function(e) {
					let signatureFields = e.target.querySelectorAll(".nrf-widget.signature");
					signatureFields.forEach(function(field) {
						field.signature.clear();
					});
				});
			});
		}
	});
');

$cssVars = [
	'line-color' => $field->form['params']->get('inputcolor')
];

$cssVars = ConvertForms\FieldsHelper::cssVarsToString($cssVars, '.'. $field->input_id);

ConvertForms\Helper::addStyleDeclarationOnce($cssVars);

echo $class->toWidget([
	'pen_color' => $field->form['params']->get('inputcolor'),
	'name' => $field->input_name,
	'css_class' => ' ' . implode(' ', [$field->size, $field->cssclass])
]);