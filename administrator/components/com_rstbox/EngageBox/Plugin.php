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

namespace EngageBox;

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Plugin\CMSPlugin;

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

/**
 *  Engage Box Type main class used by Engage Box plugins
 */
class Plugin extends CMSPlugin
{
    /**
     *  Auto load plugin's language file
     *
     *  @var  boolean
     */
    protected $autoloadLanguage = true;

    /**
     *  Application Object
     *
     *  @var  object
     */
    protected $app;

    /**
     *  Returns Plugin's base path
     *
     *  @return  string
     */
    protected function getPath()
    {
        return JPATH_PLUGINS . '/engagebox/' . $this->name . '/';
    }

    
    /**
     *  Event ServiceName - Returns the service information
     *
     *  @return  array
     */
    public function onEngageBoxTypes(&$types)
    {
        $types[$this->name] = \JText::_('PLG_ENGAGEBOX_' . strtoupper($this->name) . '_ALIAS');
    }
    

    /**
     * Render plugin's layout. Users can override it in the /templates/TEMPLATE_NAME/plg_engagebox_PLUGINNAME folder
     *
     * @param object $box   The box object
     *
     * @return mixed null on failure, string on success
     */
    public function onEngageBoxTypeRender($box)
    {
        if ($box->boxtype !== $this->name)
        {
            return;
        }

		ob_start();
		include \JPluginHelper::getLayoutPath('engagebox', $this->name);
		return ob_get_clean();
    }

    /**
     *  Prepare form.
     *
     *  @param   JForm  $form  The form to be altered.
     *  @param   mixed  $data  The associated data for the form.
     *
     *  @return  boolean
     */
    public function onContentPrepareForm($form, $data)
    {
        // Return if we are in frontend or we don't have a valid form
        if ($this->app->isClient('site'))
        {
            return true;
        }

        // Check we have a valid form context
        if ($form->getName() != 'com_rstbox.item')
        {
            return true;
        }

        // When item is not saved yet, the $data variable is type of Array.
        $tempData = (object) $data;

        if (!isset($tempData->boxtype) || $tempData->boxtype != $this->name)
        {
            return true;
        }

        // Try to load form
        try
        {
            $form->loadFile($this->getForm(), false);
        }
        catch (Exception $e)
        {
            $this->app->enqueueMessage($e->getMessage(), 'error');
        }

        return true;
    }

    /**
     *  Get Plugin's form path
     *
     *  @return  string
     */
    protected function getForm()
    {
        $path = $this->getPath() . '/form/form.xml';

        if (\JFile::exists($path))
        {
            return $path;
        }
    }
}