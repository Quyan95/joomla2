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
?>
<div class="footer text-center">
    
    <?php echo JText::_('COM_RSTBOX') . ' v' . NRFramework\Functions::getExtensionVersion('com_rstbox', true); ?>
    <br>

    <?php if ($this->config->get('showcopyright', true)) { ?>
        <div class="footer_review">
            <?php echo JText::_('NR_LIKE_THIS_EXTENSION'); ?>
            <a href="https://extensions.joomla.org/extensions/extension/style-a-design/popups-a-iframes/engage-box" target="_blank"><?php echo JText::_('NR_LEAVE_A_REVIEW'); ?></a> 
            <a href="https://extensions.joomla.org/extensions/extension/style-a-design/popups-a-iframes/engage-box" target="_blank" class="stars"><span class="icon-star"></span><span class="icon-star"></span><span class="icon-star"></span><span class="icon-star"></span><span class="icon-star"></span></a>
        </div>

        <br>&copy; <?php echo JText::sprintf('NR_COPYRIGHT', date('Y')) ?><br>
    <?php } ?>
</div>