<?php

/**
 * @package         EngageBox
 * @version         6.1.4 Pro
 * 
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            http://www.tassos.gr
 * @copyright       Copyright © 2020 Tassos Marinos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Plugin\CMSPlugin;

class plgEngageBoxPHPScripts extends CMSPlugin
{
    /**
     * The box object
     *
     * @var object
     */
    private $box;

    /**
     * Payload sent to the php scripts.
     * 
     * @var  array
     */
    private $payload = [];

    /**
     *  Auto load plugin's language file
     *
     *  @var  boolean
     */
    protected $autoloadLanguage = true;

    /**
     * Add PHP Scripts form into the box editing page
     *
     * @param  object $form
     *
     * @return void
     */
    public function onContentPrepareForm($form)
    {
        if ($form->getName() != 'com_rstbox.item')
        {
            return;
        }

        $form->loadFile(__DIR__ . '/form.xml', false);
    }

    
    /**
     * The BeforeRender event fires before the box's layout is ready.
     *
     * @param  object $box           The box's settings object
     *
     * @return void
     */
    public function onEngageBoxBeforeRender($box)
    {
        $this->box = $box;
        $this->runPHPScript('beforerender');
    }

    /**
     * The AfterRender event fires after the box's layout is ready.
     *
     * @param  string $boxLayout     The box's final HTML output
     * @param  object $box           The box's settings object
     *
     * @return void
     */
    public function onEngageBoxAfterRender(&$boxLayout, $box)
    {
        $this->box = $box;
        $this->payload = ['boxLayout' => &$boxLayout];
        $this->runPHPScript('afterrender');
    }

    /**
     * The Open event fires every time the box opens
     *
     * @param  object $box  The box's settings object
     *
     * @return void
     */
    public function onEngageBoxOpen($box)
    {
        $this->box = $box;
        $this->runPHPScript('open');
    }

    /**
     * Close event fires every time the box closes
     *
     * @param  object $box  The box's settings object
     *
     * @return void
     */
    public function onEngageBoxClose($box)
    {
        $this->box = $box;
        $this->runPHPScript('close');
    }

    /**
     * Run user-defined PHP scripts
     *
     * @param   String  $script   The PHP code to run
     *
     * @return  void
     */
    private function runPHPScript($php_script)
    {
        if (!$php_script = $this->box->params->get('phpscripts.' . $php_script))
        {
            return;
        }

        $this->payload['box'] = $this->box;

        // Run PHP
        (new \NRFramework\Executer($php_script, $this->payload))->run();
    }
    
}