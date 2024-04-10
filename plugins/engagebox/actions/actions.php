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
use Joomla\Registry\Registry;

class plgEngageBoxActions extends CMSPlugin
{
    /**
     * Action object
     *
     * @var object
     */
    protected $action;

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

        $form->addFieldPath(__DIR__ . '/form/fields');
        $form->loadFile(__DIR__ . '/form/form.xml', false);
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
        if (!$actions = $box->params->get('actions'))
        {
            return;
        }

        $js = '';

        foreach ($actions as $action)
        {
            $this->action = new Registry($action);

            // Make sure the action is enabled
            if (!$this->action->get('enabled', true))
            {
                continue;
            }

            // Validate we have a valid event type
            if (!$this->action->get('when'))
            {
                continue;
            }

            // Validate action does exist
            $actionMethod = '_' . $this->action->get('do');
            if (!method_exists($this, $actionMethod))
            {
                continue;
            }
 
            // Convert delay from sec to millsec.
            $this->action->set('delay', $this->action->get('delay', 0) * 1000);

            // Get action's script
            if (!$method_result = $this->$actionMethod())
            {
                continue;
            }

            // Wrap the code with the event listener block
            $method_result = 'me.on("' . $this->action['when'] . '", function() { ' . $method_result . ' });';

            // Anonymise code block
            $js .= $this->anonymise($method_result);
        }

        if (empty($js))
        {
            return;
        }

        $js = '
            <!-- EngageBox #' . $box->id . ' Actions Start -->
            ' . $this->anonymise(' 
                if (!EngageBox) {
                    return;
                }

                EngageBox.onReady(function() {
                    var me = EngageBox.getInstance(' . $box->id . ');

                    if (!me) {
                        return;
                    }

                    ' . $js . '
                });
            ') . '
            <!-- EngageBox #' . $box->id . ' Actions End -->
        ';

        JFactory::getDocument()->addScriptDeclaration($js);
    }
   
    /**
     * The script for the "Open a Box" action. It opens the specified the box.
     *
     * @return string
     */
    private function _OpenBox()
    {
        if (!$this->action['box'])
        {
            return;
        }
        
        return $this->delayFunction('EngageBox.getInstance(' . $this->action['box'] . ').open();');
    }

    /**
     * The script for the "Close a Box" action. It closes the specified the box.
     *
     * @return string
     */
    private function _CloseBox()
    {
        if (!$this->action['box'])
        {
            return;
        }
        
        return $this->delayFunction('EngageBox.getInstance(' . $this->action['box'] . ').close();');
    }

    /**
     * The script for the "Close all opened Boxes" action. It used the closeAll() static method to close all boxes.
     *
     * @return string
     */
    private function _CloseAll()
    {
        return 'EngageBox.closeAll();';
    }

    /**
     * The script for the "Destroy Box" action. It destroys the box instance.
     *
     * @return string
     */
    private function _DestroyBox()
    {
        if (!$this->action['box'])
        {
            return;
        }
        
        return 'EngageBox.getInstance(' . $this->action['box'] . ').destroy();';
    }

    /**
     * The script for the "Redirect to a URL" action. It redirects the visitor to a URL.
     *
     * @return string
     */
    private function _GoToURL()
    {   
        $target = $this->action->get('newtab', false) ? '_blank' : '_self';
        return 'window.open("' . $this->action['url'] . '", "' . $target . '")';
    }

    /**
     * The script for the "Reload Page" action.
     *
     * @return string
     */
    private function _ReloadPage()
    {   
        return 'location.reload();';
    }

    /**
     * The script for the "Run Javascript" action. It executes the custom Javascript code specified by the administrator.
     *
     * @return string
     */
    private function _Custom()
    {
        return $this->action['customcode'];
    }

    /**
     * Execute code block with a delay
     *
     * @param  string $function
     *
     * @return string
     */
    private function delayFunction($function)
    {
        $delay = $this->action['delay'];

        if ($delay == 0)
        {
            return $function;
        }

        return 'setTimeout(function() { ' . $function . ' }, ' . $delay . ');';
    }

    /**
     * Protect code scope by wrapping it with an anonymous fuction
     *
     * @param  string $string   The code to anonymise
     *
     * @return string
     */
    private function anonymise($string)
    {
        // Keep the new line character inside return for code presentation purposes.
        return '
        !(function() { ' . $string . ' })();';
    }
    
}