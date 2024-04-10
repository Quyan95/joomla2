<?php

/**
 * @package         Convert Forms
 * @version         4.2.4 Pro
 * 
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2023 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Plugin\CMSPlugin;

class PlgConvertFormsToolsGATracker extends CMSPlugin
{
    /**
     *  Application Object
     *
     *  @var  object
     */
    protected $app;

    /**
     *  Auto loads the plugin language file
     *
     *  @var  boolean
     */
    protected $autoloadLanguage = true;

    /**
     *  Add plugin fields to the form
     *
     *  @param   JForm   $form  
     *  @param   object  $data
     *
     *  @return  boolean
     */
    public function onConvertFormsFormPrepareForm($form, $data)
    {
        $form->loadFile(__DIR__ . '/form/form.xml', false);
        return true;
    }

    /**
     * Event triggered during fieldset rendering in the form editing page in the backend.
     *
     * @param string $fieldset_name The name of the fieldset is going to be rendered
     * @param string $fieldset      The HTML output of the fieldset
     *
     * @return void
     */
    public function onConvertFormsBackendFormPrepareFieldset($fieldset_name, &$fieldset)
    {
        if ($this->_name != $fieldset_name)
        {
            return;
        }

        $tracking_id = $this->params->get('tracking_id');
        
        // Proceed only if Tracking ID is not set yet.
        if (!empty($tracking_id))
        {
            return;
        }

        $extension_id = NRFramework\Extension::getID($this->_name, 'plugin', 'convertformstools');
		$url = \JURI::base() . '/index.php?option=com_plugins&task=plugin.edit&extension_id=' . $extension_id;

        $warning = '
            <div class="alert alert-error">
                <span class="icon-warning"></span>' .
                \JText::_('PLG_CONVERTFORMSTOOLS_GATRACKER_TRACKING_ID_MISSING') . '
                <a onclick=\'window.open("' . $url . '", "cfgatracker", "width=1000, height=750");\' href="#">' 
				. \JText::_("PLG_CONVERTFORMSTOOLS_GATRACKER_TRACKING_ID_SET") . 
			    '</a>
            </div>';
       
        $fieldset = $warning . $fieldset;
    }

    /**
     * Undocumented function
     *
     * @param [type] $form
     *
     * @return void
     */
    public function onConvertFormsFormAfterRender($html, $form)
    {
        // Only on front-end
        if ($this->app->isClient('administrator'))
        {
            return;
        }

        // Is Google Analytics Tracking enabled for this form?
        if (! (bool) $form['params']->get('gatracker.enable', false))
        {
            return;
        }

        // Make sure we have a valid tracking ID
        if (!$tracking_id = $this->params->get('tracking_id'))
        {
            return;
        }

        // Setup settings
        $doc = JFactory::getDocument();
        $options = $doc->getScriptOptions('com_convertforms');
        $options['gatracker'] = [
            'options' => [
                'tracking_id' => $tracking_id,
                'event_category' => 'Convert Forms'
            ],
            'forms' => [
                $form['id'] => [
                    'name' => $form['name']
                ]
            ]
        ];

        $doc->addScriptOptions('com_convertforms', $options);

        // Load script
        JHtml::script('plg_convertformstools_gatracker/script.js', ['relative' => true, 'version' => 'auto']);
    }
}
