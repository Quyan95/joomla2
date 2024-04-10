<?php
/**
 * @package         Cache Cleaner
 * @version         9.1.0
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            https://regularlabs.com
 * @copyright       Copyright © 2023 Regular Labs All Rights Reserved
 * @license         GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

use RegularLabs\Module\CacheCleaner\Administrator\Helper\CacheCleaner as CacheCleanerHelper;

/* @var object $params */

$text  = CacheCleanerHelper::getText();
$class = 'header-item-content'
    . ($params->add_button_text ? '' : ' rl-button-no-text')
    . ($params->button_classname ? ' ' . $params->button_classname : '');

?>
<a href="javascript:" onclick="RegularLabs.CacheCleaner.purge();" class="<?php echo $class; ?>" title="<?php echo $text; ?>">
    <div class="header-item-icon">
        <span class="icon-trash" aria-hidden="true"></span>
    </div>

    <?php if ($params->add_button_text) : ?>
        <div class="header-item-text">
            <?php echo $text; ?>
        </div>
    <?php endif; ?>
</a>
