<?php

/**
 * @package         EngageBox
 * @version         6.1.4 Pro
 * 
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            http://www.tassos.gr
 * @copyright       Copyright © 2023 Tassos Marinos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;

class JFormFieldEBButton extends JFormField
{
    /**
     * Method to get a list of options for a list input.
     *
     * @return    array   An array of JHtml options.
     */
    protected function getInput()
    {
        $extraAtts = '';

        foreach ($this->element->attributes() as $key => $value)
        {
            if (strpos($key, 'btn-') === 0)
            {
                $extraAtts .= Text::sprintf('%s="%s" ', str_replace('btn-', '', $key), (string) $value);
            }
        }

        return '
            <button class="' . (string) $this->element['class'] . '" type="button" ' . $extraAtts . '>' . Text::_((string) $this->element['button-text']) . '</button>
        ';
    }
}