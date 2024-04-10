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

$readOnly = (isset($field->readonly) && $field->readonly == '1');
$required = (isset($field->required) && $field->required == '1');

if (!$readOnly)
{
	$doc = JFactory::getDocument();

	// Load flatpickr media files
	JHtml::stylesheet('com_convertforms/vendor/flatpickr/flatpickr.css', ['relative' => true, 'version' => 'auto']);
	JHtml::script('com_convertforms/vendor/flatpickr.js', ['relative' => true, 'version' => 'auto']);

	// Load selected theme
	if ($field->theme)
	{
		JHtml::stylesheet('com_convertforms/vendor/flatpickr/' . $field->theme . '.css', ['relative' => true, 'version' => 'auto']);
	}

	// Setup plugin options
	$options = [
		'mode'	     	  => $field->mode,
		'dateFormat' 	  => $field->dateformat,
		'defaultDate'	  => $field->value,
		'minDate'	 	  => $field->mindate,
		'maxDate'	 	  => $field->maxdate,
		'enableTime'	  => (bool) $field->showtimepicker,
		'time_24hr'  	  => (bool) $field->time24,
		'minuteIncrement' => $field->minuteincrement,
		'inline'	 	  => (bool) $field->inline,
		'disableMobile'	  => isset($field->disable_mobile) ? (bool) $field->disable_mobile : false,
		'allowInput'	  => $required
	];

	// Localize
	$lang = explode('-', \JFactory::getLanguage()->getTag())[0];

	// Grrrrr! Flatpickr doesn't seem to have a strict naming rule for its language files. 
	// A language file can be named either using the ISO 639-1 language code (el) or the ISO 3166-1 country code (gr). 

	// List of locales that need to be converted. 
	$convert_language_codes = [
		'el' => 'gr' // Greek
	];
	if (array_key_exists($lang, $convert_language_codes))
	{
		$lang = $convert_language_codes[$lang];
	}

	// Skip english
	if ($lang != 'en')
	{
		$lang = strtolower($lang);
		$options['locale'] = $lang;
		$doc->addScript('https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/l10n/' . $lang . '.js');
	}

	$doc->addScriptDeclaration('
		ConvertForms.Helper.onReady(function() {
			flatpickr.l10ns.default.firstDayOfWeek = ' . (isset($field->firstDayOfWeek) ? $field->firstDayOfWeek : 1) . ';
			flatpickr("input#' . $field->input_id . '", ' . json_encode($options, JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK) . ');

			// Fix the appearance of native date picker to match the design of the rest of the inputs.
			var mobile_inputs = document.querySelectorAll(".flatpickr-mobile");
			if (mobile_inputs) {
				mobile_inputs.forEach(function(input) {
					input.setAttribute("style", input.previousSibling.getAttribute("style"));
				});
			}
		});
	');

	$doc->addStyleDeclaration('
		.flatpickr-input {
			cursor:default !important;
			background-color: var(--input-background-color) !important;
		}
		.flatpickr-current-month .flatpickr-monthDropdown-months{
			height: auto !important;
			display: inline-block !important;
			min-width: auto !important;
		}
		.flatpickr-current-month .flatpickr-monthDropdown-months:hover {
			background: none;
		}
		.numInputWrapper {
			margin-left: 10px;
		}
		.numInputWrapper:hover {
			background: none;
		}
		.flatpickr-calendar input {
			box-shadow: none !important;
		}
		.flatpickr-calendar .flatpickr-time input {
			height: auto !important;
			border: none !important;
			box-shadow: none !important;
			font-size: 16px !important;
			margin: 0 !important;
			padding: 0 !important;
			line-height: inherit !important;
			background: none !important;
			color: ' . ($field->theme == "dark" ? "#fff" : "#484848")  . ' !important;
		}
		.flatpickr-calendar.inline {
			margin-top:5px;
		}
		.flatpickr-calendar.open {
			z-index: 99999999; // EngageBox v4 uses z-index: 99999999;
		}
		.flatpickr-mobile {
			-webkit-appearance: none; // Remove iOS ugly button
			-moz-appearance: none;
			min-height:40px;
		}
		.flatpickr-calendar .numInputWrapper .cur-year {
			height: auto !important;
		}
	');
}

?>

<input type="text" name="<?php echo $field->input_name ?>" id="<?php echo $field->input_id; ?>"
	<?php if ($required) { ?>
		required
	<?php } ?>

	<?php if (isset($field->placeholder) && !empty($field->placeholder)) { ?>
		placeholder="<?php echo htmlspecialchars($field->placeholder, ENT_COMPAT, 'UTF-8'); ?>"
	<?php } ?>

	<?php if (isset($field->value) && !empty($field->value)) { ?>
		value="<?php echo $field->value; ?>"
	<?php } ?>

	<?php if ($readOnly) { ?>
		readonly
	<?php } ?>

	autocomplete="off"
	class="<?php echo $field->class ?>"
>