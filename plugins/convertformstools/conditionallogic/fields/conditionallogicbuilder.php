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

use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Text;
class JFormFieldConditionalLogicBuilder extends JFormFieldHidden
{
    /**
     * Method to get a list of options for a list input.
     *
     * @return      array           An array of JHtml options.
     */
    protected function getInput()
    {
        // Disable form rendering on textarea change
        $this->class .= ' norender';

        JHtml::script('plg_convertformstools_conditionallogic/builder.js', ['relative' => true, 'version' => 'auto']);
        JHtml::stylesheet('plg_convertformstools_conditionallogic/builder.css', ['relative' => true, 'version' => 'auto']);

        // Load translations
        $strings = LanguageHelper::parseIniFile(JPATH_PLUGINS . '/convertformstools/conditionallogic/language/en-GB/en-GB.plg_convertformstools_conditionallogic.ini');
        
        $extraKeys = [
            'NR_OR',
        ];

        $strings = array_merge(array_keys($strings), $extraKeys);

        foreach ($strings as $key)
        {
            Text::script($key);
        }

        $rulesOnly = isset($this->element['rulesOnly']) ? (bool) $this->element['rulesOnly'] : false;

        if ($rulesOnly)
        {
            return '
                <h4>' . Text::_('PLG_CONVERTFORMSTOOLS_CONDITIONALLOGIC_PROCESS_THIS') . '</h4>
                <div class="clb">
                    <div id="clr-' . $this->id . '"></div>
                    ' . parent::getInput() . '
                </div>
            ';
        }

        return '<button type="submit" data-bs-target="#cfcl"  data-target="#cfcl" data-bs-toggle="modal" data-toggle="modal" class="cf-btn clstart">' . Text::_('PLG_CONVERTFORMSTOOLS_CONDITIONALLOGIC_SETUP') . '</button>' . parent::getInput();
    }
}