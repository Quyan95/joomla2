<?php
/**
 * @copyright     Copyright (c) 2009-2021 Ryan Demmer. All rights reserved
 * @license       GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses
 */
defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Language\Text;

?>
<div class="uk-form-row uk-margin-small-bottom uk-grid uk-grid-small">
  <label for="popup_src" class="uk-form-label uk-width-1-5 hastip" title="<?php echo Text::_('WF_LABEL_URL_DESC'); ?>"><?php echo Text::_('WF_LABEL_URL'); ?></label>
  <div class="uk-form-controls uk-width-4-5">
    <input id="popup_src" type="text" value="" class="uk-input-multiple-disabled browser files" />
  </div>
</div>
