<?php
/**
 * @package     JCE
 * @subpackage  Editor
*
 * @copyright   Copyright (c) 2009-2023 Ryan Demmer. All rights reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

class WFCaptionPlugin extends WFEditorPlugin
{
    /**
     * Constructor activating the default information of the class.
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function display()
    {
        parent::display();

        $document = WFDocument::getInstance();
        $settings = $this->getSettings();

        $document->addScriptDeclaration('CaptionDialog.settings=' . json_encode($settings) . ';');

        $tabs = WFTabs::getInstance(
            array(
                'base_path' => WF_EDITOR_PLUGIN,
            )
        );

        // Add tabs
        $tabs->addTab('text', 1);
        $tabs->addTab('container', 1);

        // add link stylesheet
        $document->addStyleSheet(array('caption'), 'plugins');
        // add link scripts last
        $document->addScript(array('caption'), 'plugins');
    }
}
