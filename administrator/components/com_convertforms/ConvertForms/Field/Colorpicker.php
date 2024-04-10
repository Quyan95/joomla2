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

class ColorPicker extends \ConvertForms\Field
{
	/**
	 *  Remove common fields from the form rendering
	 *
	 *  @var  mixed
	 */
	protected $excludeFields = [
		'size',
		'placeholder',
		'browserautocomplete',
		'inputmask',
		'inputcssclass'
	];

	/**
	 *  Set field object
	 *
	 *  @param  mixed  $field  Object or Array Field options
	 */
	public function setField($field)
	{
		// Joomla's Color field in the Field settings returns 'none' if the input is left blank 
		// which makes the CF input to have 'none' as the default value in the backend.
		// The code below fixes that issue.
		if ($field['value'] == 'none')
		{
			$field['value'] = '';	
		}

		$field['placeholder'] = '#000000';

		parent::setField($field);

		return $this;
	}
}