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

?>

<div class="calculation-builder-toolbar">
    <div>
        <select class="norender">
            <option disabled selected value="">- <?php echo JText::_('PLG_CONVERTFORMSTOOLS_CALCULATIONS_ADD_FIELD') ?> -</option>
        </select>
    </div>
    <div>
        <button>1</button>
        <button>2</button>
        <button>3</button>
        <button>4</button>
        <button>5</button>
        <button>6</button>
        <button>7</button>
        <button>8</button>
        <button>9</button>
        <button>0</button>
    </div>
    <div>
        <button>.</button>
        <button title="<?php echo JText::_('PLG_CONVERTFORMSTOOLS_CALCULATIONS_ADD') ?>">+</button>
        <button title="<?php echo JText::_('PLG_CONVERTFORMSTOOLS_CALCULATIONS_SUBTRACT') ?>">-</button>
        <button title="<?php echo JText::_('PLG_CONVERTFORMSTOOLS_CALCULATIONS_DIVIDE') ?>">/</button>
        <button title="<?php echo JText::_('PLG_CONVERTFORMSTOOLS_CALCULATIONS_MULTIPLY') ?>">*</button>
        <button title="<?php echo JText::_('PLG_CONVERTFORMSTOOLS_CALCULATIONS_MODULUS') ?>">%</button>
        <button title="<?php echo JText::_('PLG_CONVERTFORMSTOOLS_CALCULATIONS_START_GROUP') ?>">(</button>
        <button title="<?php echo JText::_('PLG_CONVERTFORMSTOOLS_CALCULATIONS_END_GROUP') ?>">)</button>
        <button class="backspace" title="<?php echo JText::_('PLG_CONVERTFORMSTOOLS_CALCULATIONS_UNDO') ?>"><span class="icon-reply"></span></button>
        <button class="clear" title="<?php echo JText::_('PLG_CONVERTFORMSTOOLS_CALCULATIONS_CLEAR_FORMULAO') ?>"><span class="icon-purge"></span></button>
    </div>
</div>