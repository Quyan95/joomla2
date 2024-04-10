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

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

use Joomla\Registry\Registry;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Log\Log;

class PlgSystemCFUploadedFilesCleaner extends CMSPlugin
{
    /**
     * Remove old uploaded files based on the preferences set in each File Upload Field.
     *
     * @return void
     */
    public function onConvertFormsCronTask($task, $options = array())
    {
		if ($task != 'uploadedfilescleaner')
		{
			return;
        }

        $this->log('Starting Uploaded Folders Cleaner - Time Limit: ' . $options['time_limit'] . 's');
        
        // Start the clock!
        $clockStart = microtime(true);
        
        // Load all files to be deleted
        $files = $this->getToBeDeletedFiles();

        if (!$files || !is_array($files) || empty($files))
        {
            $this->log('No upload files found that need cleaning. Abort.');
            return;
        }

        $processedCount = 0;

        foreach ($files as $key => $file)
        {
            if (!\JFile::delete($file))
            {
                continue;
            }
        
            $processedCount++;
        
            // Timeout check -- Only if we did delete a file!
            $clockNow = microtime(true);
            $elapsed  = $clockNow - $clockStart;

            if (($options['time_limit'] > 0) && ($elapsed > $options['time_limit']))
            {
                $leftOvers = count($files) - $processedCount;
                $this->log("I ran out of time. Number of files in queue left unprocessed: $leftOvers");
                return;
            }
        }

        $this->log("$processedCount files deleted");
        $this->log('Finishing Uploaded Folders Cleaner');
    }

    /**
	 * Get an array of all Fileupload field files to be deleted.
	 *
	 * @return  array
	 */
	private function getToBeDeletedFiles()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select('id, params')
			->from('#__convertforms')
            ->where($db->quoteName('state') . ' = 1');

		$db->setQuery($query);

        $forms = $db->loadObjectList();

        if (!is_array($forms) || empty($forms))
        {
            return;
        }

        // Replace Smart Tags in the upload folder value
        $SmartTags = new \NRFramework\SmartTags();

        $to_be_deleted_files = [];
        
        foreach ($forms as $form)
        {
            $params = json_decode($form->params);            

            if (!isset($params->fields))
            {
                continue;
            }

            foreach ($params->fields as $key => $field)
            {
                $field = new Registry($field);

                if ($field->get('type') !== 'fileupload')
                {
                    continue;
                }

                $auto_delete_files = (int) $field->get('auto_delete_files', 0);

                if ($auto_delete_files == 0)
                {
                    continue;
                }

                // Find the files of the field from the DB
                if (!$files = $this->findFileuploadFieldFiles($form->id, $field->get('name'), $auto_delete_files))
                {
                    continue;
                }

                $to_be_deleted_files = array_merge($to_be_deleted_files, $files);
            }
        }

        return $to_be_deleted_files;
    }

    /**
     * Delete all files of each Fileupload field within a form based on $auto_delete_files value.
     * 
     * @param   int     $form_id
     * @param   string  $field_name
     * @param   int     $auto_delete_files
     * 
     * @return  array
     */
    private function findFileuploadFieldFiles($form_id, $field_name, $auto_delete_files)
    {
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select('params')
			->from('#__convertforms_conversions')
            ->where($db->quoteName('form_id') . ' = ' . (int) $form_id);

		$db->setQuery($query);

        $fields = $db->loadObjectList();

        $files = [];

        $this->log("Processing form #" . $form_id . " for valid files to delete");
        $this->log("Searching for files older than $auto_delete_files days");

        foreach ($fields as $key => $field)
        {
            $params = json_decode($field->params);

            if (!isset($params->$field_name))
            {
                continue;
            }

            foreach ($params->$field_name as $file)
            {
                $file_path = JPATH_ROOT . $file;

                if (!file_exists($file_path))
                {
                    continue;
                }

                $diff_in_miliseconds = time() - filemtime($file_path);

                // Skip the file if it's not old enough
                if ($diff_in_miliseconds < (60 * 60 * 24 * $auto_delete_files))
                {
                    continue;
                }

                $files[] = $file_path;
            }
        }

        $this->log("Found " . count($files) . " files to delete");

        return $files;
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
        try {
            Log::add($msg, Log::DEBUG, 'convertforms.cron.uploadedfilescleaner');
        } catch (\Throwable $th) {
        }
    }
}
