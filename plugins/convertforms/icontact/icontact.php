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

class plgConvertFormsIContact extends \ConvertForms\Plugin
{
	/**
	 *  Main method to store data to service
	 *
	 *  @return  void
	 */
	public function subscribe()
	{
		$api = new NR_iContact(array(
			'appID'          => $this->lead->campaign->appID,
			'username'       => $this->lead->campaign->username,
			'appPassword'    => $this->lead->campaign->appPassword,
			'accountID'      => $this->lead->campaign->accountID,
			'clientFolderID' => $this->lead->campaign->clientFolderID
		));
		
		$api->subscribe(
			$this->lead->email,
			$this->lead->params,
			$this->lead->campaign->list
		);

		if (!$api->success())
		{
			throw new Exception($api->getLastError());
		}
	}

	/**
	 *  Retrieve the accountID and the clientFolderID
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

		if (!is_object($article) || !isset($article->params) || !isset($article->service) || ($article->service != 'icontact'))
		{
			return;
		}

		$params = json_decode($article->params);

		if (!isset($params->appID) || !isset($params->username) || !isset($params->appPassword))
		{
			return;
		}

		$this->loadWrapper();

		if (empty($params->accountID) || empty($params->clientFolderID))
		{
			try
			{
				$api = new NR_iContact(array(
					'appID'       => $params->appID,
					'username'    => $params->username,
					'appPassword' => $params->appPassword
				));
				$params->accountID      = $api->accountID;
				$params->clientFolderID = $api->clientFolderID;
				$article->params        = json_encode($params);
			}
			catch (Exception $e)
			{
				JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
			}
		}

		return true;
	}
}