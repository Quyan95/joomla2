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

if (isset($button->show) && !$button->show)
{ 
	return;
}

// Close box on redirection? Defaults to Yes.
$button->close = !isset($button->close) ? true : (bool) $button->close;

?>

<a
	<?php if ($button->click == "url") { ?>
		<?php if ($button->close) { ?>
		data-ebox-prevent="0"
		data-ebox-cmd="close"
		<?php } ?>
		target="<?php echo $button->newtab ? "_blank" : "_self" ?>"
		href="<?php echo $button->url ?>"
		rel="noopener"
	<?php } ?>

	<?php if ($button->click == "open") { ?>
		data-ebox-cmd="open"
		data-ebox="<?php echo $button->box; ?>"
		href="#"
	<?php } ?>

	<?php if ($button->click == "close") { ?>
		data-ebox-cmd="close"
		href="#"
	<?php } ?>

	<?php 
		$styles = implode(';', array(
			"background-color:" . $button->background,
			"color:" 	 . $button->color,
			"min-width:" . (int) $yesno->get("buttonwidth", "100") . "px"
		));
	?>

	class="ebox-ys-btn"
	style="<?php echo $styles; ?>">
	<?php echo $button->text ?>

	<?php if (isset($button->subtext) && !empty($button->subtext)) { ?>
		<span class="ebox-ys-subtext"><?php echo $button->subtext ?></span>
	<?php } ?>
</a>