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

class PlgConvertFormsToolsConditionalLogic extends CMSPlugin
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
     * Render the modal box when the form editor loads
     *
     * @return void
     */
    public function onConvertFormsEditorView()
    {
        echo JHtml::_('bootstrap.renderModal', 'cfcl', [
            'title'  => \JText::_('PLG_CONVERTFORMSTOOLS_CONDITIONALLOGIC_FIELDS'),
            'backdrop' => 'static',
            'footer' => '
                <a style="float:left; margin-right:auto;" href="https://www.tassos.gr/joomla-extensions/convert-forms/docs/conditional-fields" class="cf-btn" target="_blank">
                    ' . JText::_('JHELP') . '
                </a>
                <button type="button" class="cf-btn" data-bs-dismiss="modal" data-dismiss="modal" style="margin-right:4px;" aria-hidden="true">'. JText::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</button>
                <a href="#" class="btn btn-success cf-menu-item save" aria-hidden="true" data-cfaction="save">
                    <i class="cf-icon-ok up-state">' . JText::_('JAPPLY') .' </i>
                    <i class="cf-icon-spin hover-state">Saving..</i>
                </a>
            '
        ], '<div id="clb-root" class="inputSettings"></div>');
    }

    /**
	 *  Validate the conditions during save
	 *
	 *  @param   string  $context  The context of the content passed to the plugin (added in 1.6)
	 *  @param   object  $article  A JTableContent object
	 *  @param   bool    $isNew    If the content has just been created
	 *
	 *  @return  boolean
	 */
	public function onContentBeforeSave($context, $form, $isNew)
	{
		if ($context != 'com_convertforms.form')
		{
			return;
        }

        if (!is_object($form) || !isset($form->params))
        {
            return;
        }

        $params = json_decode($form->params);

        // Proceed only if Conditional Logic is available and enabled
        if (!isset($params->conditionallogic) || !$params->conditionallogic->enable)
        {
            return true;
        }

        $conditions = json_decode($params->conditionallogic->rules, true);

        if (!$conditions)
        {
            return true;
        }

        // Reset keys
        $conditions = array_values($conditions);

        $fields = $params->fields;

        foreach ($conditions as $conditionKey => $condition)
        {
            // Check rules for missing values
            foreach ($condition['rules'] as $rules)
            {
                $error_prefix = 'Conditional Logic: Condition #' . ($conditionKey + 1) . ' - ';

                foreach ($rules as $rule)
                {
                    if (empty($rule))
                    {
                        $form->setError($error_prefix . 'Rule is invalid');
                        return false;
                    }

                    // Check if the field exists
                    if (!$this->fieldExist($fields, $rule['field']))
                    {
                        $form->setError($error_prefix . 'Rule field is invalid');
                        return false;
                    }

                    // Check that we have a valid comparator
                    if (!isset($rule['comparator']) || $rule['comparator'] == '')
                    {
                        $form->setError($error_prefix . 'Rule operator is missing');
                        return false;
                    }

                    // Check that we have a valid rule value
                    if (isset($rule['arg']) && $rule['arg'] == '' && !in_array($rule['comparator'], ['is_checked', 'not_checked', 'empty', 'not_empty']))
                    {
                        $form->setError($error_prefix . 'Rule value is empty');
                        return false;
                    }
                }
            }
        }

        return true;
    }

    public function fieldExist($fields, $field_id)
    {
        foreach ($fields as $key => $field)
        {
            if ($field->key == $field_id)
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine whether the form has calculations in order to load the respective scripts.
     *
     * @param string $html  The form's final HTML layout.
     * @param object $form  The form object
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

        if (!$cl = $form['params']->get('conditionallogic'))
        {
            return;
        }

        if (!$cl->enable || empty($cl->rules))
        {
            return;
        }

        $conditions = json_decode($cl->rules, true);

        // Abort if we don't have any conditions
        if (!$conditions)
        {
            return;
        }

        // Setup settings
        $doc = JFactory::getDocument();
        $options = $doc->getScriptOptions('com_convertforms');
        $options['conditional_logic'][$form['id']] = $conditions;
        $doc->addScriptOptions('com_convertforms', $options);

        // Load scripts
        JHtml::script('plg_convertformstools_conditionallogic/fields.js', ['relative' => true, 'version' => 'auto']);

        // When CSS is not loaded, the .cf-hide class must be declared in order for the Show/Hide actions to work.
        if (!ConvertForms\Helper::getComponentParams()->get('loadCSS', true))
        {
            $doc->addStyleDeclaration('
                .cf-hide {
                    display:none;
                    pointer-events: none;
                }
            ');
        }
    }
    

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
        $form->loadFile(__DIR__ . '/form/form.xml');
    }
}
