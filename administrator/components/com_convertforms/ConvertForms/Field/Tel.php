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

use Joomla\CMS\Filter\InputFilter;
use NRFramework\Countries;

class Tel extends \ConvertForms\Field
{
	protected $inheritInputLayout = 'text';
	
	/**
	 *  Renders the field's input element
	 *
	 *  @return  string  	HTML output
	 */
	protected function getInput()
	{
		return parent::getInput();
	}

	
	/**
	 *  Set field object
	 *
	 *  @param  mixed  $field  Object or Array Field options
	 */
	public function setField($field)
	{
        parent::setField($field);
		
		$enable_country_selector = isset($field['enable_country_selector']) && $field['enable_country_selector'] === '1';

		// If "Display Country Code Selector" is disabled, inherit the layout from the Text field, and set the input_type to "tel"
		if (!$enable_country_selector)
		{
			$this->field->input_type = 'tel';
			return $this;
		}

		// Otherwise, use the new Phone Number layout
		$this->inheritInputLayout = 'tel';
		
		if (is_scalar($field['value']))
		{
			$code = '';
			
			$default_country_option = isset($field['default_country_option']) ? $field['default_country_option'] : 'detect';

			switch ($default_country_option)
			{
				case 'custom':
					$code = $field['default_country_custom'];
					break;

				case 'detect':
					$code = \NRFramework\Helpers\Geo::getVisitorCountryCode();
					break;

				default:
					$code = isset($field['default_country']) && !empty($field['default_country']) ? $field['default_country'] : $code;
					break;
			}

			$this->field->value = [
				'code'  => strtoupper($code),
				'value' => $field['value']
			];
		}

		return $this;
	}

	/**
	 *  Validate field value
	 *
	 *  @param   mixed  $value           The field's value to validate
	 *
	 *  @return  mixed                   True on success, throws an exception on error
	 */
	public function validate(&$value)
	{
		// Country selector is enabled. We expect an assoc array: ['code' => 'AF', 'value' => '123456789']
		if ($this->isCountrySelectorEnabled())
		{
			$isRequired = $this->field->get('required');
			
			// Sanity check
			if ($isRequired && (empty($value) || !is_array($value) || !isset($value['code']) || !isset($value['value'])))
			{
				$this->throwError(\JText::sprintf('COM_CONVERTFORMS_FIELD_REQUIRED'));
			}

			$value['code']  = InputFilter::getInstance()->clean($value['code'], 'WORD');
			$value['value'] = $this->filterInput($value['value']);

			// Ensure we have a valid country code
			if ($isRequired && (empty($value['value']) || (empty($value['code']) || !Countries::getCallingCodeByCountryCode($value['code']))))
			{
				$this->throwError(\JText::sprintf('COM_CONVERTFORMS_FIELD_REQUIRED'));
			}
			
			return;
		}

		parent::validate($value);
	}

	/**
	 * Returns whether "Display Country Code Selector" is enabled.
	 * 
	 * @return  bool
	 */
	protected function isCountrySelectorEnabled()
	{
		return $this->field->get('enable_country_selector') === '1';
	}

	/**
	 * This is useful when we want to prepare the field value prior to sending it to the integration.
	 * 
	 * @param   mixed  $value
	 * 
	 * @return  mixed
	 */
	public function prepareRawValue($value)
	{
		return $this->prepareValue($value);
	}

	/**
	 * Prepares the value.
	 * 
	 * @param   mixed   $value
	 * 
	 * @return  string
	 */
	public function prepareValue($value = '')
	{
		if (!$value)
		{
			return;
		}
		
		if (is_scalar($value))
		{
			return $value;
		}

		// If Country Selector is not enabled, skip
		if (!$this->isCountrySelectorEnabled())
		{
			return $value;
		}

		return $this->prepareValueWithCountryCode($value);
	}

	/**
	 * Prepares the value which is an array and contains both a "code" (calling code) and "value" (phone number).
	 * 
	 * @param   array   $value
	 * 
	 * @return  string
	 */
	public function prepareValueWithCountryCode($value = [])
	{
		$value = (array) $value;
		
		if ((!isset($value['code']) || !isset($value['value'])) || (empty($value['code']) || empty($value['value'])))
		{
			return;
		}

		$calling_code = Countries::getCallingCodeByCountryCode($value['code']);
		$calling_code = $calling_code !== '' ? '+' . $calling_code : '';
		
		return $calling_code . $value['value'];
	}
	

	/**
	 * Prepare value to be displayed to the user as HTML/text
	 *
	 * @param   mixed   $value
	 *
	 * @return  string
	 */
	public function prepareValueHTML($value)
	{
		$value = $this->prepareValue($value);

		
		if (!is_scalar($value))
		{
			$value = $this->prepareValueWithCountryCode($value);
		}
		
		
		return '<a href="tel:' . $value . '">' . $value . '</a>';
	}
}