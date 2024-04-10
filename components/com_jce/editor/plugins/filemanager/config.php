<?php
/**
 * @package     JCE
 * @subpackage  Editor
 *
 * @copyright   Copyright (c) 2009-2023 Ryan Demmer. All rights reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

 defined('JPATH_PLATFORM') or die;

class WFFilemanagerPluginConfig
{
    public static function getConfig(&$settings)
    {
        require_once __DIR__ . '/filemanager.php';

        $plugin = new WFFileManagerPlugin();

        $config = array();

        if ($plugin->getParam('inline_upload', 1) && $plugin->getParam('upload', 1)) {
            $config['upload'] = array(
                'max_size'  => $plugin->getParam('max_size', 1024),
                'filetypes' => $plugin->getFileTypes(),
                'inline' => true
            );
        }

        $settings['filemanager'] = $config;

        // remove "object" from invalid elements
        $settings['invalid_elements'] = array_diff($settings['invalid_elements'], array('object'));
    }
}
