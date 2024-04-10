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

$plugin = $box->params->get("socialplugin");
$async  = $box->params->get("async", "afterOpen") == "dom" ? false : $box->params->get("async", "afterOpen");
$lang   = ($box->params->get("sociallang", "auto") == "auto") ? str_replace("-","_",JFactory::getLanguage()->getTag()) : "en_US";
$FB_F   = '(function(d, s, id) {var js, fjs = d.getElementsByTagName(s)[0];if (d.getElementById(id)) return;js = d.createElement(s); js.id = id;js.src = "//connect.facebook.net/'.$lang.'/sdk.js#xfbml=1&version=v2.5";fjs.parentNode.insertBefore(js, fjs);}(document, "script", "facebook-jssdk"));';
$TW_F   = '!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?"http":"https";if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+"://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document, "script", "twitter-wjs");';
$header = $box->params->get("socialtext", null);

$content = ($plugin == "twfollow") ? $TW_F : $FB_F;

if (!$async)
{ 
	JFactory::getDocument()->addScriptDeclaration($content); 
} else
{
	JFactory::getDocument()->addScriptDeclaration('
		EngageBox.onReady(function() {
			var box     = EngageBox.getInstance(' . $box->id . ');
			var content = ' . json_encode($content) .';
			var async   = ' . json_encode($async) .';

			if (async == "pageLoad") { 

				window.addEventListener("load", function() {
					eval(content);
				});
			} else {
				box.on(async, function() {
					eval(content);
				});
			}
		});
	');
}

?>

<?php if (!empty($header)) { ?>
	<div class="eb-content-header">
		<?php echo $box->params->get("socialtext") ?>
	</div>
<?php } ?>
<div class="eb-content-wrap">
	<?php if ($plugin == "fbpagelike") { ?>
		<div class="fb-page"
			data-href="<?php echo $box->params->get("socialurl") ?>" 
			data-tabs="<?php echo implode(",",$box->params->get("fbtabs", array())) ?>"
			data-width="<?php echo $box->params->get("socialwidth"); ?>"
			data-height="<?php echo $box->params->get("socialheight"); ?>"
			data-small-header="<?php echo $box->params->get("fbsmallheader", "false") ?>" 
			data-adapt-container-width="<?php echo $box->params->get("fbadaptwidth", "true") ?>"
			data-hide-cover="<?php echo $box->params->get("fbhidecover", "false") ?>" 
			data-show-facepile="<?php echo $box->params->get("fbfacepile", "true") ?>">
		</div>
		<div id="fb-root"></div>
	<?php } ?>
	<?php if ($plugin == "fbpost") { ?>
		<div class="fb-post" data-href="<?php echo $box->params->get("social_fb_post_url") ?>" data-width="<?php echo $box->params->get("socialwidth"); ?>"></div>
		<div id="fb-root"></div>
	<?php } ?>
	<?php if ($plugin == "twfollow") { ?>
		<a href="https://twitter.com/<?php echo $box->params->get("social_tw_hanbdle")?>"
			rel="noopener"
			class="twitter-follow-button"
			data-show-screen-name="<?php echo $box->params->get("social_tw_showusername", false) ? "true" : "false"  ?>"
			data-show-count="<?php echo $box->params->get("social_tw_count", false) ? "true" : "false"  ?>"
			data-size="<?php echo $box->params->get("social_tw_largebutton") ? "large" : "" ?>">
		</a>
	<?php } ?>
</div>