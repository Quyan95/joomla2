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

class plgConvertFormsElasticEmail extends \ConvertForms\Plugin
{
	/**
	 *  Main method to store data to service
	 *
	 *  @return  void
	 */
	public function subscribe()
	{
		$api = new NR_ElasticEmail(array('api' => $this->lead->campaign->api));
		$api->subscribe(
			$this->lead->email,
			$this->lead->campaign->list,
			$this->lead->campaign->publicAccountID,
			$this->lead->params,
			$this->lead->campaign->updateexisting,
			$this->lead->campaign->doubleoptin,
			$this->lead->campaign->publicAccountID
		);

		if (!$api->success())
		{
			throw new Exception($api->getLastError());
		}
	}

	/**
	 *  Get the publicAccountID
	 *
	 *  @param   string  $context  The context of the content passed to the plugin (added in 1.6)
	 *  @param   object  $article  A JTableContent object
	 *  @param   bool    $isNew    If the content has just been created
	 *
	 *  @return  boolean
	 */
	public function onContentBeforeSave($context, $article, $isNew)
	{
		if ($context != 'com_convertforms.campaign')
		{
			return;
		}

		if (!is_object($article) || !isset($article->params) || !isset($article->service) || ($article->service != 'elasticemail'))
		{
			return;
		}

		$this->loadWrapper();

		$params = json_decode($article->params);

		if (!isset($params->api))
		{
			return;
		}

		try
		{
			$api                     = new NR_ElasticEmail(array('api' => $params->api));
			$params->publicAccountID = $api->getPublicAccountID();
			$article->params         = json_encode($params);
		}
		catch (Exception $e)
		{
			JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
			return;
		}

		return true;
	}
}