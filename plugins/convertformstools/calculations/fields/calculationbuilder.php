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

class JFormFieldCalculationBuilder extends JFormFieldTextarea
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

        $layout = new \JLayoutFile('calculationbuilder_tmpl', __DIR__);
        $html = $layout->render();

        JHtml::script('plg_convertformstools_calculations/calculation_builder.js', ['relative' => true, 'version' => 'auto']);

        return '<div class="calculation-builder">' . $html . parent::getInput() . '</div>';
    }
}