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

use NRFramework\Executer;
use Joomla\CMS\Language\Text;
use ConvertForms\Tasks\App;

class plgConvertFormsAppsPHP extends App
{
	/**
	 * The trigger that sends the email
	 *
	 * @return void
	 */
	public function actionPHP()
	{
        try
        {
            $executer = new Executer($this->options['php'], $this->payload);
            return $executer->run();
        } catch (\Throwable $th)
        {
            $this->setError($th->getMessage());
        }
	}

    /**
     * Get a list with the fields needed to setup the app's event.
     *
     * @return array
     */
	public function getActionPHPSetupFields()
	{
        return [
            [
                'name' => Text::_('COM_CONVERTFORMS_APP_SETUP_ACTION'),
                'fields' => [
                    $this->field('php', ['type' => 'textarea']),
                ]
            ]
        ];
	}

    /**
     * Override the die() method to not include the app's name in the error
     *
     * @param  string $error    The error message
     * 
     * @return void
     */
    public function die($error = null)
    {
        $message = is_null($error) ? $this->errors[0] : $error;
        throw new \Exception($message);
    }
}