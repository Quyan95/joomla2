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

JLoader::register('PDFHelper', __DIR__ . '/helper/pdfhelper.php');

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Log\Log;

class PlgConvertFormsToolsPDF extends CMSPlugin
{
    /**
     *  Application Object
     *
     *  @var  object
     */
    protected $app;

    
    /**
     * The absolute URL of the generated PDF.
     * 
     * @var  string
     */
    private $generated_pdf = '';

    /**
     * Whether the PDF has been created.
     * 
     * @var  boolean
     */
    private $created_pdf = false;
    

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

        
        \JFactory::getDocument()->addScriptDeclaration('
            jQuery(function($) {
                var pdfEnabled = false;
                var pdfToggle = document.querySelector("#jform_pdf_pdf_enabled");
                if (pdfToggle) {
                    pdfEnabled = pdfToggle.checked;
                    pdfToggle.addEventListener(\'change\', function(evt) {
                        pdfEnabled = evt.target.checked;
                    });
                }
                $(document).on("smartTagsBoxBeforeRender", function(event, tags, element) {
                    if (tags.Submission && pdfEnabled) {
                        tags.Submission["{submission.pdf}"] = "Submission PDF";
                    }
                });
            });
        ');
        
    }

    
	/**
	 * The form event. Load additional parameters when available into the field form.
	 * Only when the type of the form is of interest.
	 *
	 * @param   JForm     $form  The form
	 * @param   stdClass  $data  The data
	 *
	 * @return  bool
	 */
    public function onContentPrepareForm($form, $data)
    {
        // Return if we are in frontend or we don't have a valid form
        if ($this->app->isClient('site') || !($form instanceof JForm))
        {
            return true;
        }

        // Check we have a valid form context
        if ($form->getName() != 'com_convertforms.conversion')
        {
            return true;
        }

        // Try to load form
        try
        {
            $form->loadFile(__DIR__ . '/form/submission.xml', false);
        }
        catch (Exception $e)
        {
            $this->app->enqueueMessage($e->getMessage(), 'error');
        }

        return true;
    }

    /**
     *  Creates the PDF and adds the Smart Tag {submission.pdf} with the absolute URL as value
     * 
     *  @param   string  $submission  The Submission data
     *  @param   string  $data 		  Array of data to append the generated PDF
     * 
     *  @return  void
     */
    public function onConvertFormsGetSubmissionSmartTags($submission, &$data)
    {
        // Only on the front-end
        if (\JFactory::getApplication()->isClient('administrator'))
        {
            return;
        }
        
		// Make sure PDF is enabled
		if (!PDFHelper::isPDFEnabled($submission))
		{
			return;
        }

        // Run only once
        if ($this->created_pdf)
        {
            // return already generated PDF
            $data['pdf'] = $this->generated_pdf;
            return;
        }
        
        $this->created_pdf = true;
        
        // Add PDF to Smart Tags list only if a PDF was validated & created
        if (!$this->generated_pdf = PDFHelper::createSubmissionPDF($submission))
        {
            return;
        }
        
        $data['pdf'] = $this->generated_pdf;
    }

    /**
     * Renders the PDF of the submission when viewing a submission on the front-end
     * 
     * @param   object  $submission
     * 
     * @return  void
     */
    public function onConvertFormsFrontSubmissionViewInfo($submission)
    {
        // Only on the front-end
        if (\JFactory::getApplication()->isClient('administrator'))
        {
            return;
        }

		// Make sure PDF is enabled
		if (!PDFHelper::isPDFEnabled($submission))
		{
			return;
        }

		if (!$file_path = \ConvertForms\SubmissionMeta::getValue($submission->id, 'pdf'))
		{
            return;
		}
		
        ?>
        <tr>
            <th><?php echo \JText::_('PLG_CONVERTFORMSTOOLS_PDF_LABEL'); ?></th><td><a href="<?php echo JURI::root() . $file_path; ?>" target="_blank"><?php echo \JText::_('PLG_CONVERTFORMSTOOLS_PDF_SUBMISSION_VIEW_BTN'); ?></a></td>
        </tr>
        <?php
    }

    /**
     *  Update PDF whenever the submission is updated in the backend.
     *
     *  @param   string  $context  The context of the content passed to the plugin (added in 1.6)
     *  @param   object  $article  A JTableContent object
     *
     *  @return  void
     */
    public function onContentAfterSave($context, $article)
    {
        if ($context != 'com_convertforms.conversion' || !$this->app->isClient('administrator'))
        {
            return;
        }

        // Load submission details
        $model = JModelLegacy::getInstance('Conversion', 'ConvertFormsModel', ['ignore_request' => true]);
        if (!$submission = $model->getItem($article->id))
        {
            return;
        }

        // Update PDF with the updated submission data
        if (PDFHelper::isPDFEnabled($submission))
        {
            // Update submission
            if ($submission_file_path = \ConvertForms\SubmissionMeta::getValue($submission->id, 'pdf'))
            {
                $path = implode(DIRECTORY_SEPARATOR, [JPATH_ROOT, $submission_file_path]);
                
                PDFHelper::updateSubmissionPDF($path, $submission);
            }
        }
    }

    /**
     * Remove old PDFs from the server
     *
     * @return void
     */
    public function onConvertFormsCronTask($task, $options = array())
    {
		if ($task != 'cleanexpiredpdf')
		{
			return;
        }

        $this->log('Starting PDF Cleaner - Time Limit: ' . $options['time_limit'] . 's');

        $db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select($db->quoteName(array('sm.id', 'cf.name', 'cf.params', 'sm.meta_value', 'sm.date_created'), array('id', 'form_name', 'params', 'file_path', 'date_created')))
            ->from($db->quoteName('#__convertforms_submission_meta', 'sm'))
            ->join('LEFT', $db->quoteName('#__convertforms_conversions', 'c') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('sm.submission_id'))
            ->join('LEFT', $db->quoteName('#__convertforms', 'cf') . ' ON ' . $db->quoteName('cf.id') . ' = ' . $db->quoteName('c.form_id'))
            ->where($db->quoteName('sm.meta_type') . ' = \'pdf\'');

		$db->setQuery($query);

        $files = $db->loadObjectList();

        if (!is_array($files) || empty($files))
        {
            $this->log('No files found that need cleaning. Abort.');
            return;
        }

        $files_to_delete = count($files);

        // store all deleted submissions to delete later
        $deleted_submissions = [];
        
        // Start the clock!
        $clockStart = microtime(true);

        $processedCount = 0;
        
        foreach ($files as $key => $file)
        {
            $params = json_decode($file->params);

            if (!isset($params->pdf))
            {
                continue;
            }

            // pdf enabled check
            if (!isset($params->pdf->pdf_enabled))
            {
                continue;
            }
            
            if ($params->pdf->pdf_enabled != '1')
            {
                continue;
            }

            $this->log("Processing form: " . $file->form_name . " and file: " . $file->file_path);

            // delete PDFs after
            $delete_after = $params->pdf->pdf_remove_after;

            $this->log("Searching if file is older than $delete_after days");

            // never clean
            if ($delete_after == 'never')
            {
                continue;
            }

            $full_file_path = JPATH_ROOT . '/' . $file->file_path;

            // check if file exists
            if (!\JFile::exists($full_file_path))
            {
                continue;
            }

            $diff_in_milliseconds = time() - strtotime($file->date_created);

            // Skip the file if it's not old enough
            if ($diff_in_milliseconds < (60 * 60 * 24 * $delete_after))
            {
                continue;
            }

            // delete file
            if(\JFile::delete($full_file_path))
            {
                $deleted_submissions[] = $file->id;
                $processedCount++;

                // Timeout check -- Only if we did delete a file!
                $clockNow = microtime(true);
                $elapsed  = $clockNow - $clockStart;

                if (($options['time_limit'] > 0) && ($elapsed > $options['time_limit']))
                {
                    $leftOvers = count($files_to_delete) - $processedCount;
                    $this->log("I ran out of time. Number of files in queue left unprocessed: $leftOvers");
                    break;
                }
            }

        }

        // delete submission meta
        if ($deleted_submissions)
        {
            \ConvertForms\SubmissionMeta::deleteAll($deleted_submissions);
        }

        $this->log("$processedCount files deleted");

        $this->log('PDF Cleaner Stopped');
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
            Log::add($msg, Log::DEBUG, 'convertforms.cron.cleanexpiredpdf');
        } catch (\Throwable $th) {
        }
    }
    
}
