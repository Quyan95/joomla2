<?php

/**
 * @package         EngageBox
 * @version         6.1.4 Pro
 * 
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            http://www.tassos.gr
 * @copyright       Copyright © 2021 Tassos Marinos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

defined('_JEXEC') or die('Restricted access');

\JHtml::_('behavior.core');

JFactory::getDocument()->addScriptDeclaration('
    window.copyToClipoard = (text) => {
        navigator.clipboard.writeText(text).then(function() {
            Joomla.renderMessages({"success":[`${text} Smart Tag copied to clipboard`]});
        }, function(err) {
            Joomla.renderMessages({"error":[`Error copying to clipboard`]});
        });
    }
');

JFactory::getDocument()->addStyleDeclaration('
    .ctc {
        cursor:pointer;
    }
    .ctc span {
        display:none;
    }
    .ctc:hover span {
        display:inline-block;
    }
');

?>

<table class="adminlist table table-striped">
    <thead>
        <tr>
            <th width="20%">Syntax</th>
            <th width="40%">Description</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($this->tags as $tag => $tagvalue) { ?>
            <tr>
                <td style="font-family:consolas">
                    <span class="ctc" onClick="copyToClipoard('<?php echo $tag ?>');" title="Click to copy to clipboard">
                        <?php echo $tag ?>
                        <span class="icon-copy"></span>
                    </span>
                </td>
                <td><?php echo JText::_('NR_TAG_' . strtoupper(str_replace(array('{', '}', '.'), '', $tag))) ?></td>
            </tr>
        <?php } ?>
    </tbody>
</table>