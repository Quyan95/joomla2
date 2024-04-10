<?php

/**
 * @package         EngageBox
 * @version         6.1.4 Pro
 * 
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            http://www.tassos.gr
 * @copyright       Copyright © 2020 Tassos Marinos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

defined('_JEXEC') or die('Restricted access');

$box = $displayData;
?>
<button type="button" data-ebox-cmd="close" class="eb-close placement-<?php echo $box->close_button_placement; ?>" aria-label="Close">
	<img alt="close popup button" />
	<span aria-hidden="true">&times;</span>
</button>