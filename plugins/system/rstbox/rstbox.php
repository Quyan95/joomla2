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

use Joomla\CMS\Plugin\CMSPlugin;

jimport('joomla.filesystem.file');

/**
 *  EngageBox Render Plugin
 */
class PlgSystemRstBox extends CMSPlugin
{
    /**
     *  Application Object
     *
     *  @var  object
     */
    protected $app;

    /**
     *  Boxes final HTML layout
     *
     *  @var  string
     */
    private $html;

    /**
     *  Component's param object
     *
     *  @var  JRegistry
     */
    private $param;

    /**
     *  The loaded indicator of helper
     *
     *  @var  boolean
     */
    private $init;

    /**
     *  onAfterDispatch Event
     */
    public function onAfterDispatch()
    {
        // Get Helper
        if (!$this->getHelper())
        {
            return;
        }

        if ($this->app->input->get('template_preview', null))
        {
            return;
        }

        // Hide a popup via query string
        if ($popups_to_hide = (array) $this->app->input->getInt('engagebox_hide'))
        {
            foreach ($popups_to_hide as $popup_to_hide)
            {
                $cookie = new EngageBox\Cookie($popup_to_hide);
                $cookie->set();
            }
        }
       
        $this->html = EngageBox\Boxes::render();
    }

    
    /**
     *  Handles the content preparation event fired by Joomla!
     *
     *  @param   mixed     $context     Unused in this plugin.
     *  @param   stdClass  $article     An object containing the article being processed.
     *  @param   mixed     $params      Unused in this plugin.
     *  @param   int       $limitstart  Unused in this plugin.
     *
     *  @return  bool
     */
    public function onContentPrepare($context, &$article, &$params, $limitstart = 0)
    {
        // Get Helper
        if (!$this->getHelper())
        {
            return;
        }

        \EngageBox\Helper::replaceShortcodes($article->text);
    }
    

    /**
     *  Listening to the onAfterRender event in order to append the boxes to the document
     */
    public function onAfterRender() 
    {
        // Get Helper
        if (!$this->getHelper())
        {
            return;
        }

        // Break if no boxes found
        if (!$html = $this->html)
        {
            return;
        }

        // Prepare replacements
        $buffer = $this->app->getBody();
        $closingTag = '</body>';

        if (strpos($buffer, $closingTag))
        {
            // If </body> exists prepend the box HTML
            $buffer = str_replace($closingTag, $html . $closingTag, $buffer);
        } else 
        {
            // If </body> does not exist append to document's end
            $buffer .= $html;
        }
        
        // Set body's final layout
        $this->app->setBody($buffer);
    }

    /**
     *  Method to handle AJAX requests.
     *  If not passed a valid token the request will abort.
     *  
     *  Listening on URL: ?option=com_ajax&format=raw&plugin=rstbox&task=track
     *
     *  @return  JSON result formated in JSON
     */
    public function onAjaxRstbox()
    {
        error_reporting(E_ALL & ~E_NOTICE);

        // JSession::checkToken('request') or die('Invalid Token');

        // Check if a valid task passed
        if (!$task = $this->app->input->get('task', null))
        {
            die('Invalid Task');
            return;
        }

        // Check if task method exists
        $taskMethod = 'task' . ucfirst($task);
        if (!method_exists($this, $taskMethod))
        {
            die('Task not found');
            return;
        }

        // Initialize EngageBox Library
        if (!@include_once(JPATH_ADMINISTRATOR . '/components/com_rstbox/autoload.php'))
        {
            return;
        }

        // Run method
        $this->$taskMethod();

        // Stop execution
        jexit();
    }

    private function taskTrackEvent()
    {
        // Get the event name
        if (!$event = $this->app->input->get('event', null, 'WORD'))
        {
            die('Invalid Event');
            return;
        }

        // Make sure we have a valid box ID passed
        if (!$box_id = $this->app->input->get('box', null, 'INT'))
        {
            die('Invalid Box ID');
            return;
        }

        // Load box settings
        if (!$box = EngageBox\Box::get($box_id))
        {
            return;
        }

        // Trigger Open & Close Event
        \JPluginHelper::importPlugin('engagebox');
        $this->app->triggerEvent('onEngageBox' . ucfirst($event), [$box]);

        $options = $this->app->input->get('options', '', 'array');

        $response['success'] = true;

        if ($event == 'open')
        {
            // Do not track when box is on test mode
            if (!$box->testmode)
            {
                EngageBox\Box::logOpenEvent($box_id);
            }
        }
        
        // Log impression in the database
        if ($event == 'close')
        {
            // Do not set any cookie if box is on test mode
            if (!$box->testmode && !isset($options['temporary']))
            {
                $cookie = new EngageBox\Cookie($box_id);
                $cookie->set();

                if ($cookie->exist())
                {
                    $response['action'] = 'stop';
                }
            }
        }

        JFactory::getDocument()->setMimeEncoding('application/json');

        echo json_encode($response);
    }

    /**
     *  Loads the helper classes of plugin
     *
     *  @return  bool
     */
    private function getHelper()
    {
        // Return if is helper is already loaded
        if ($this->init)    
        {
            return true;
        }

        // Return if we are not in frontend
        if (!$this->app->isClient('site'))
        {
            return false;
        }

        // Return if compnent is not enabled
        $component = JComponentHelper::getComponent('com_rstbox', true);

        if (!$component->enabled)
        {   
            return;
        }

        // Run only on HTML pages
        if ($this->app->input->get('format', 'html', 'cmd') != 'html' || JFactory::getDocument()->getType() !== 'html')
        {
            return false;
        }

        // Initialize EngageBox Library
        if (!@include_once(JPATH_ADMINISTRATOR . '/components/com_rstbox/autoload.php'))
        {
            return false;
        }

        // Load Tassos Framework
        if (!@include_once(JPATH_PLUGINS . '/system/nrframework/autoload.php'))
        {
            return false;
        }
        
        // Return if document type is Feed
        if (NRFramework\Functions::isFeed())
        {
            return false;
        }

        return ($this->init = true);
    }
}