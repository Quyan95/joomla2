<?php

/**
 * @package         EngageBox
 * @version         6.1.4 Pro
 * 
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            http://www.tassos.gr
 * @copyright       Copyright © 2023 Tassos Marinos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

defined('_JEXEC') or die('Restricted access');

$box = $displayData;

$cssClasses = [
	'eb-' . $box->id,
	'eb-floating-button',
	'eb-' . $box->params->get('floating_button_position')
];

if ($box->triggermethod !== 'floatingbutton')
{
	$cssClasses[] = 'eb-hide';
}

if ($box->triggermethod == 'floatingbutton')
{
	// Always show the button when the trigger is the floatingbutton itself
	$state = true;
	
	// but, if the popup is configured to remain hidden on close, do not show the floating button again.
	if ($box->params->get('cookietype') !== 'never')
	{
		$state = false;
	}

	$box->params->set('floating_button_show_on_close', $state);
}

$doc = JFactory::getDocument();
$doc->addScriptDeclaration('
	EngageBox.onReady(() => {
		const popup = EngageBox.getInstance(' . $box->id . ');
		const button = document.querySelector(".eb-floating-button.eb-' . $box->id . '");
		const showOnClose = '. ($box->params->get('floating_button_show_on_close') ? 'true' : 'false') .';

		popup.on("close", () => {
			if (showOnClose) {
				button.classList.remove("eb-hide");
			}
		}).on("open", () => {
			button.classList.add("eb-hide");
		});
	})
');

$vars = [
	'color' => $box->params->get('floatingbutton_message.textcolor', '#fff'),
	'bgColor' => $box->params->get('floatingbutton_message.bgcolor', '#4285F4'),
	'fontSize' => (int) $box->params->get('floatingbutton_message.fontsize', 16) . 'px'
];

$cssVars = NRFramework\Helpers\CSS::cssVarsToString($vars, '.eb-' . $box->id . '.eb-floating-button');
$doc->addStyleDeclaration($cssVars);

?>

<div class="<?php echo implode(' ' , $cssClasses) ?>">
	<div data-ebox="<?php echo $box->id ?>" data-ebox-delay="0">
		<?php echo $box->params->get('floatingbutton_message.text') ?>
	</div>
</div>