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

$css = @file_get_contents(JPATH_ROOT . '/media/plg_system_nrframework/css/widgets/signature.css');

echo '
	<style>
		' . $css . '
		.' . $field->input_id . ' {
			--line-color: ' . $field->form['params']->get('inputcolor') . ';
		}
	</style>
';

echo $class->toWidget([
	'pen_color' => $field->form['params']->get('inputcolor'),
	'name' => $field->input_name,
	'css_class' => ' ' . implode(' ', [$field->size, $field->cssclass])
]);

?>
