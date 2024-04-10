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

$height  = $box->params->get("iframeheight", "500px");
$url     = $box->params->get("iframeurl");
$scroll  = $box->params->get("iframescrolling", "no");
$params  = $box->params->get("iframeparams");
$async   = $box->params->get("iframeasync", "afterOpen") == "dom" ? false : $box->params->get("iframeasync", "afterOpen");
$header  = $box->params->get("iframeheader", null);
$class   = ($height == "100%") ? "eboxFitFrame" : "";
$content = '<div class="iframeWrapper">' .
				'<iframe width="100%" height="' . $height . '" src="' . $url . '" scrolling="' . $scroll . '" frameborder="0" allowtransparency="true" ' . $params . ' class="' . $class . '"></iframe>' .
			'</div>';

?>

<?php if ($header) ?>
	<div class="eb-content-header">
		<?php echo $box->params->get("iframeheader"); ?>
	</div>
<?php ?>

<div class="eb-content-wrap">
	<?php if (!$async) { echo $content; } ?> 
</div>

<?php 

if ($async)
{ 
	JFactory::getDocument()->addScriptDeclaration('
		EngageBox.onReady(function() {
			var box 	   = EngageBox.getInstance(' . $box->id . ');
			var async      = ' . json_encode($async) .';
			var content    = ' . json_encode($content) .';
			var container  = box.el.querySelector(".eb-content-wrap");
			var removeOnClose = '.json_encode($box->params->get("removeonclose", false)).'

			if (async == "pageLoad") {
				window.addEventListener("load", function() {
					container.innerHTML = content;
				});
			} else {
				box.on(async, function() {
					if (container.querySelectorAll("iframe").length == 0) {
						container.innerHTML = content;
					}
				});
			}

			if (removeOnClose) {
				box.on("afterClose", function() {
					container.removeChild(container.querySelector(".iframeWrapper"));
				});
			}
		});'
	);
}