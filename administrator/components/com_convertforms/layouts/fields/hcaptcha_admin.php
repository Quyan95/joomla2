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

// no hCaptcha image is present in invisible-mode
if ($field->hcaptcha_type == 'invisible')
{
	return;
}

$suffix = $field->size == 'compact' ? '_compact' : '';

$imageURL = JURI::root() . 'media/com_convertforms/img/hcaptcha_' . $field->theme . $suffix . '.png';
?>
<img src="<?php echo $imageURL ?>" style="align-self: flex-start;" />