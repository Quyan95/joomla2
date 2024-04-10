<?php
/**
 * @package     JCE
 * @subpackage  Editor
 *
 * @copyright   Copyright (c) 2009-2023 Ryan Demmer. All rights reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;

final class WFSourcePlugin extends WFEditorPlugin
{
    public function __construct($config = array())
    {
        // Call parent
        parent::__construct();

        $language = Factory::getLanguage();
        $language->load('com_jce_pro', JPATH_SITE);
    }

    public function display()
    {
        $document = WFDocument::getInstance();

        $view = $this->getView();

        $view->addTemplatePath(WF_EDITOR_PLUGIN . '/tmpl');

        $theme = $this->getParam('source.theme', 'codemirror');

        $document->addScript(array(
            'jquery.min',
        ), 'jquery');

        $document->addScript(array(
            'plugin.min.js',
        ), 'media');

        $document->addStyleSheet(array(
            'plugin.min.css',
        ), 'media');

        $document->addScript(array(
            'editor.min',
        ), 'plugins');

        $document->addScript(array(
            'pro/vendor/beautify/beautify.min',
            'pro/vendor/codemirror/js/codemirror.min',
        ), 'jce');

        $document->addStyleSheet(array(
            'editor.min',
        ), 'plugins');

        $document->addStyleSheet(array(
            'pro/vendor/codemirror/css/codemirror.min',
            'pro/vendor/codemirror/css/theme/' . $theme,
        ), 'jce');

        // add custom codemirror.css theme if exists
        if (is_file(JPATH_SITE . '/media/jce/css/codemirror.css')) {
            $document->addStyleSheet(array('media/jce/css/codemirror.css'), 'joomla');
        }

        // keep as ltr for source code
        $document->setDirection('ltr');
    }
}
