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

use Joomla\Registry\Registry;
use ConvertForms\Export;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Log\Log;

class PlgConvertFormsToolsExportSubmissions extends CMSPlugin
{
    /**
     * The Joomla application object
     *
     * @var object
     */
    protected $app;

    /**
     * Remove old uploaded files based on the preferences set in each File Upload Field.
     *
     * @return void
     */
    public function onConvertFormsCronTask($task, $options = array())
    {
		if ($task !== 'exportsubmissions')
		{
			return;
        }

        // Start the clock!
        $clockStart = microtime(true);

        $this->log('Start: Export Form Submissions - Time Limit: ' . $options['time_limit'] . 's');

        $input = $this->app->input;
        
        if (!$formID = $input->getInt('form_id'))
        {
            $this->die('Form ID is missing');
        }

        $options = [
            // Accepts an integer representing the ID of the form.
            'filter_form_id'       => $formID,

            // Accepts any search term.
            'filter_filter_search' => $input->get('filter_search'),

            // Accepts: today, yesterday, this_week, this_month, this_year, last_week, last_month, last_year, daterange
            'filter_period'        => $input->get('filter_period'),
            'filter_created_from'  => $input->get('filter_created_from'),
            'filter_created_to'    => $input->get('filter_created_to'),

            // Accepts: csv, json
            'export_type'          => $input->get('export_type', 'csv'),

            // Accepts: true or false
            'export_append'        => $input->get('export_append', false),
            'export_path'          => $input->get('export_path', null, 'RAW'),
            'filename'             => 'convertforms_submissions_' . $formID . '.' . $input->get('export_type', 'csv')
        ];

        try 
        {
            $this->log(print_r($options, true));
            Export::export($options);
        } catch (\Throwable $th)
        {
            $this->die($th->getMessage());
        }

        $this->log('End: Export Form Submissions');
    }
    
    /**
     * Log debug message to lg file
     *
     * @param  strng $msg
     *
     * @return void
     */
    private function log($msg)
    {
        try
        {
            Log::add($msg, Log::DEBUG, 'convertforms.cron.exportsubmissions');
        } catch (\Throwable $th)
        {
        }
    }

    private function die($msg)
    {
        $this->log($msg, Log::ERROR);
        jexit($msg);
    }
}
