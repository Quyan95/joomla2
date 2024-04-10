<?php
/**
 * @package     JCE
 * @subpackage  Editor
 *
 * @copyright   Copyright (c) 2009-2023 Ryan Demmer. All rights reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Language\Text;

?>
<div class="media_option shockwave">
	<h4><?php echo Text::_('WF_MEDIAMANAGER_SHOCKWAVE_OPTIONS'); ?></h4>
	<div class="uk-form-row uk-grid uk-grid-small">
		<label for="shockwave_swstretchstyle" class="uk-form-label uk-width-1-5"><?php echo Text::_('WF_MEDIAMANAGER_SHOCKWAVE_SWSTRETCHSTYLE'); ?></label>
		<div class="uk-width-4-5 uk-grid uk-grid-small">
			<div class="uk-form-controls uk-width-2-5">
				<select id="shockwave_swstretchstyle">
					<option value="none"><?php echo Text::_('JNONE'); ?></option>
					<option value="meet"><?php echo Text::_('WF_MEDIAMANAGER_SHOCKWAVE_MEET'); ?></option>
					<option value="fill"><?php echo Text::_('WF_MEDIAMANAGER_SHOCKWAVE_FILL'); ?></option>
					<option value="stage"><?php echo Text::_('WF_MEDIAMANAGER_SHOCKWAVE_STAGE'); ?></option>
				</select>
			</div>
			<label for="shockwave_swvolume" class="uk-form-label uk-width-1-5"><?php echo Text::_('WF_MEDIAMANAGER_SHOCKWAVE_VOLUME'); ?></label>
			<div class="uk-form-controls uk-width-2-5">
				<input type="text" id="shockwave_swvolume" />
			</div>
		</div>
	</div>

	<div class="uk-form-row uk-grid uk-grid-small">
		<label for="shockwave_swstretchhalign" class="uk-form-label uk-width-1-5"><?php echo Text::_('WF_MEDIAMANAGER_SHOCKWAVE_SWSTRETCHHALIGN'); ?></label>
		<div class="uk-width-4-5 uk-grid uk-grid-small">
			<div class="uk-form-controls uk-width-2-5">
				<select id="shockwave_swstretchhalign">
					<option value="none"><?php echo Text::_('JNONE'); ?></option>
					<option value="left"><?php echo Text::_('WF_OPTION_LEFT'); ?></option>
					<option value="center"><?php echo Text::_('WF_OPTION_CENTER'); ?></option>
					<option value="right"><?php echo Text::_('WF_OPTION_RIGHT'); ?></option>
				</select>
			</div>
			<label for="shockwave_swstretchvalign" class="uk-form-label uk-width-1-5"><?php echo Text::_('WF_MEDIAMANAGER_SHOCKWAVE_SWSTRETCHVALIGN'); ?></label>
			<div class="uk-form-controls uk-width-2-5">
				<select id="shockwave_swstretchvalign">
					<option value="none"><?php echo Text::_('JNONE'); ?></option>
					<option value="meet"><?php echo Text::_('WF_OPTION_TOP'); ?></option>
					<option value="fill"><?php echo Text::_('WF_OPTION_CENTER'); ?></option>
					<option value="stage"><?php echo Text::_('WF_OPTION_BOTTOM'); ?></option>
				</select>
			</div>
		</div>
	</div>
	<div class="uk-form-row uk-grid uk-grid-small">
		<label for="shockwave_autostart" class="uk-form-label uk-width-1-5"><input type="checkbox" class="checkbox" id="shockwave_autostart" /> <?php echo Text::_('WF_MEDIAMANAGER_SHOCKWAVE_AUTOSTART'); ?></label>
		<label for="shockwave_sound" class="uk-form-label uk-width-1-5"><input type="checkbox" class="checkbox" id="shockwave_sound" checked="checked" /> <?php echo Text::_('WF_MEDIAMANAGER_SHOCKWAVE_SOUND'); ?></label>
	</div>
	<div class="uk-form-row uk-grid uk-grid-small">
		<label for="shockwave_swliveconnect" class="uk-form-label uk-width-1-5"><input type="checkbox" class="checkbox" id="shockwave_swliveconnect" /> <?php echo Text::_('WF_MEDIAMANAGER_SHOCKWAVE_LIVECONNECT'); ?></label>
		<label for="shockwave_progress" class="uk-form-label uk-width-1-5"><input type="checkbox" class="checkbox" id="shockwave_progress" checked="checked" /> <?php echo Text::_('WF_MEDIAMANAGER_SHOCKWAVE_PROGRESS'); ?></label>
	</div>
</div>