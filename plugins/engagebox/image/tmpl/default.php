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

$image    = new Joomla\Registry\Registry($box->params->get("image"));

$source   = $image->get("type", "1") == "1" ? JURI::root() . $image->get("imagefile") : $image->get("imageurl");
$onclick  = $image->get("onclick", "url");
$target   = $image->get("newtab") ? "_blank" : "_self";
$url      = $onclick == "url" ? $image->get("url", "#") : "#";
$cmd      = $image->get("cookie") ? "closeKeep" : "close";
$alt      = $image->get("alt");
$width    = $image->get("width", "auto");
$height   = $image->get("height", "auto");
$class 	  = $image->get("class");

?>

<a data-ebox-cmd="<?php echo $cmd ?>"
	<?php if ($onclick == "url") { ?>
		data-ebox-prevent="0"
		target="<?php echo $target ?>"
		rel="noopener"
	<?php } ?>
	href="<?php echo $url; ?>">
	<img 
		src="<?php echo $source ?>"
		width="<?php echo $width ?>"
		height="<?php echo $height ?>"
		alt="<?php echo $alt ?>"
		class="<?php echo $class?>"
	/>
</a>