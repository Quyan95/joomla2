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

namespace ConvertForms\Field;

defined('_JEXEC') or die('Restricted access');

class Currency extends \ConvertForms\FieldChoice
{
	protected $inheritInputLayout = 'dropdown';

	/**
	 *  Set the field choices
	 *
	 *  @return  array  The field choices array
	 */
    protected function getChoices()
    {
		require_once JPATH_PLUGINS . '/system/nrframework/fields/currencies.php';

		$class = new \JFormFieldNR_Currencies();
		$currencies = $class->currencies;

		asort($currencies);

		$choices = array();

		foreach ($currencies as $currencyCode => $currencyName)
		{
			switch ($this->field->display)
			{
				case '2':
					$label = $currencyCode;
					break;	
				case '3':
					$label = $currencyName . ' (' . $currencyCode . ')';	
					break;	
				default:
					$label = $currencyName;
					break;
			}

			$choices[] = array(
				'label'    => $label,
				'value'    => $currencyCode,
				'selected' => strtolower($this->field->value) == strtolower($currencyCode)
			);
		}

		// If we have a placeholder available, add it to dropdown choices.
        if (isset($this->field->placeholder) && !empty($this->field->placeholder))
        {
            array_unshift($choices, array(
                'label'    => trim($this->field->placeholder),
                'value'    => '',
                'selected' => true,
                'disabled' => true
            ));
        }

		return $choices;
    }
	
    /**
	 * Event fired during form saving in the backend to help us validate user options.
	 *
	 * @param  object	$model			The Form Model
	 * @param  array	$form_data		The form data to be saved
	 * @param  array	$field_options	The field data
	 *
	 * @return bool
	 */
    public function onBeforeFormSave($model, $form_data, &$field_options)
	{
		// The Country field has no choices to search for, so we always return true
		$this->prepareBeforeFormSave($field_options);

		return true;
	}
}