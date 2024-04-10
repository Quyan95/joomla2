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

use NRFramework\URLHelper;
use ConvertForms\SmartTags;

class PDFHelper
{
	/**
	 * PDF HTML Template
	 * 
	 * @var  String
	 */
	public static $html = '<!DOCTYPE HTML><html><head><meta http-equiv="content-type" content="text/html; charset=utf-8"><style type="text/css">* { font-family: arial !important; }</style></head><body>[TEMPLATE]</body>';

	/**
	 * Validates and creates the PDF
	 * 
	 * @param   object  $submission
	 * 
	 * @return  void
	 */
	public static function createSubmissionPDF($submission)
	{
		// pdf enabled check
		if (!self::isPDFEnabled($submission))
		{
			return;
		}

		$save_data_to_db = !isset($submission->form->save_data_to_db) || (isset($submission->form->save_data_to_db) && $submission->form->save_data_to_db === '1');

		$pdf = $submission->form->pdf;
		
		// Prepare the PDF's template with content plugins. 
		// This enables us to create a shared template with the Custom Module and 
		// load it across different forms using the {loadmoduleid ID} syntax.
		$pdf['pdf_template'] = \JHtml::_('content.prepare', $pdf['pdf_template']);

		// set filename prefix
		$prefix = (isset($pdf['pdf_filename_prefix']) && !empty($pdf['pdf_filename_prefix'])) ? $pdf['pdf_filename_prefix'] . '_' : '';

		// append the _{submission.id] suffix to each file name
		$pdf['pdf_filename'] = $prefix . '{submission.id}';
		
		// replace Smart Tags
		$pdf = SmartTags::replace($pdf, $submission);

		/**
		 * Try transiterating the file name using the native php function
		 * 
		 * This is used in Joomla 4.0 version of makeSafe but not in 3.X.
		 * 
		 * If the given filename is non-latin, then all characters will be removed from the filename via makeSafe and thus
		 * we wont be able to upload the file.
		 * 
		 * @see https://github.com/joomla/joomla-cms/pull/27974
		 */
		if (!defined('nrJ4') && function_exists('transliterator_transliterate') && function_exists('iconv'))
		{
			// Using iconv to ignore characters that can't be transliterated
			$pdf['pdf_filename'] = iconv("UTF-8", "ASCII//TRANSLIT//IGNORE", transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $pdf['pdf_filename']));
		}

		// Make safe after the Smart Tags replacement
		$pdf['pdf_filename'] = self::makeSafe($pdf['pdf_filename']);

		// upload folder check
		if (!$upload_folder = $pdf['pdf_upload_folder'])
		{
			return;
		}

		$ds = DIRECTORY_SEPARATOR;

        $upload_folder = rtrim($upload_folder, $ds);
		
		// make sure upload folder exists and is writable
		if (!\NRFramework\File::createDirs(implode($ds, [JPATH_ROOT, $upload_folder])))
		{
			return;
		}
		
		$file_path = implode($ds, [$upload_folder, $pdf['pdf_filename'] . '.pdf']);

        // make sure the file exists
		// Do we really need to check if the file exists? 
        if (!\JFile::exists(implode($ds, [JPATH_ROOT, $file_path])))
        {
			// Convert all relative paths found in <a> and <img> elements to absolute URLs
			$pdf_template = URLHelper::relativePathsToAbsoluteURLs($pdf['pdf_template']);
			
			$destination_file_path = \JPath::clean(implode(DIRECTORY_SEPARATOR, [JPATH_ROOT, $upload_folder, self::makeSafe($pdf['pdf_filename']) . '.pdf']));
			
			self::create($destination_file_path, $pdf_template, self::getOptions($pdf));

			// Create Submission Meta record only if we are saving data to the database
			if ($save_data_to_db)
			{
				\ConvertForms\SubmissionMeta::save($submission->id, 'pdf', '', $file_path);
			}

			return JURI::root() . $file_path;
		}

		return self::getSubmissionPDF($submission);
	}

	/**
	 * Update the existing submission PDF with new submission details.
	 * 
	 * @param   string  $path
	 * @param   object  $submission
	 * 
	 * @return  void
	 */
	public static function updateSubmissionPDF($path, $submission)
	{
		$pdf = $submission->form->pdf;
		
		// Prepare the PDF's template with content plugins. 
		// This enables us to create a shared template with the Custom Module and 
		// load it across different forms using the {loadmoduleid ID} syntax.
		$pdf['pdf_template'] = \JHtml::_('content.prepare', $pdf['pdf_template']);

		// replace Smart Tags
		$pdf = SmartTags::replace($pdf, $submission);

		// Convert all relative paths found in <a> and <img> elements to absolute URLs
		$pdf_template = URLHelper::relativePathsToAbsoluteURLs($pdf['pdf_template']);
		
		self::create($path, $pdf_template, self::getOptions($pdf));
	}

	/**
	 * Returns the PDF options.
	 * 
	 * @param   array  $pdf
	 * 
	 * @return  array
	 */
	public static function getOptions($pdf = [])
	{
		return [
			'paper_size' => isset($pdf['pdf_paper_size']) ? $pdf['pdf_paper_size'] : 'letter',
			'orientation' => isset($pdf['pdf_orientation']) ? $pdf['pdf_orientation'] : 'portrait'
		];
	}

	/**
	 * Creates a PDF given the upload folder and filename
	 * 
	 * @param   string  $destination  	The destination file path
	 * @param   string  $content	  	The content of the PDF
	 * @param   array   $pdf_options  	Extra options
	 * 
	 * @return  void
	 */
	public static function create($destination, $content, $pdf_options = [])
	{
		// include domPDF library
		require JPATH_SITE . '/plugins/convertformstools/pdf/dompdf/autoload.php';

		// replace [TEMPLATE] with given pdf contents
		$pdf_html = str_replace('[TEMPLATE]', $content, self::$html);

		// reference the Dompdf namespace
		$options = new \Dompdf\Options();
		$options->setIsRemoteEnabled(true);

		// instantiate and use the dompdf class
		$dompdf = new \Dompdf\Dompdf($options);

		// Set paper size and orientation
		if (isset($pdf_options['paper_size']) && isset($pdf_options['orientation']))
		{
			$dompdf->setPaper($pdf_options['paper_size'], $pdf_options['orientation']);
		}

		$dompdf->loadHtml($pdf_html);

		// Render the HTML as PDF
		$dompdf->render();
		
		$output = $dompdf->output();

		file_put_contents($destination, $output);
	}

	/**
	 * Retrieves the submission PDF
	 * 
	 * @param   object  $submission
	 * 
	 * @return  string
	 */
	public static function getSubmissionPDF($submission)
	{
		if (!isset($submission->form->pdf))
		{
			return;
		}
		
		if (!$file_path = \ConvertForms\SubmissionMeta::getValue($submission->id, 'pdf'))
		{
			return;
		}
		
        // make sure the file exists
        if (!\JFile::exists(JPATH_ROOT . '/' . $file_path))
        {
            return;
		}
		
		return JURI::root() . $file_path;
	}

	/**
	 * Sanitizes the file name
	 * 
	 * @param   string  $filename
	 * 
	 * @return  string
	 */
	public static function makeSafe($filename)
	{
		// Sanitize filename
		$filename = \JFile::makeSafe($filename);
		// Replace spaces with underscore
		$filename = str_replace(' ', '_', $filename);

		return $filename;
	}

	/**
	 * Checks whether we have PDF enabled
	 * 
	 * @param   object  $submission
	 * 
	 * @return  boolean
	 */
	public static function isPDFEnabled($submission)
	{
        return isset($submission->form->pdf['pdf_enabled']) && $submission->form->pdf['pdf_enabled'] == '1';
	}
}