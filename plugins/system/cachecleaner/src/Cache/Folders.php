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

namespace RegularLabs\Plugin\System\CacheCleaner\Cache;

defined('_JEXEC') or die;

use RegularLabs\Plugin\System\CacheCleaner\Params;

class Folders extends Cache
{
    /**
     * Empty custom folder
     */
    public static function purge_folders()
    {
    }

    /**
     * Empty tmp folder
     */
    public static function purge_tmp()
    {
        $min_age = 0;
        self::emptyFolder(JPATH_SITE . '/tmp', $min_age);
    }
}
