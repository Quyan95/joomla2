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

use ConvertForms\Validate;
use Joomla\String\StringHelper;
use Joomla\CMS\HTML\HTMLHelper;

class DateTime extends \ConvertForms\Field
{
	/**
	 *  Remove common fields from the form rendering
	 *
	 *  @var  mixed
	 */
	protected $excludeFields = array(
		'browserautocomplete',
	);

	/**
	 *  Prepares the field's input layout data in order to support PHP date relative formats such as
	 *  first day of this month, next week, +5 day e.t.c
	 *
	 *  http://php.net/manual/en/datetime.formats.relative.php
	 *  
	 *  @return  array
	 */
	protected function getInputData()
	{
		$data = parent::getInputData();

		$properties = array(
			'value',
			'mindate',
			'maxdate',
			'placeholder'
		);

		// Make sure we have a valid dateformat
		$dateFormat = $data['field']->dateformat ?: 'd/m/Y';

		foreach ($properties as $key => $property)
		{
			if (!isset($data['field']->$property) || empty($data['field']->$property))
			{
				continue;
			}

			// Try to format the date property.
			try {
				$data['field']->$property = HTMLHelper::date($data['field']->$property, $dateFormat);
			} catch (\Exception $e) {
				
			}
		}

		return $data;
	}
}

?>