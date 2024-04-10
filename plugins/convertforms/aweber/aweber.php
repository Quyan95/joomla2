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

// No direct access
defined('_JEXEC') or die('Restricted access');

class plgConvertFormsAWeber extends \ConvertForms\Plugin
{
	/**
	 *  Main method to store data to service
	 *
	 *  @return  void
	 */
	public function subscribe()
	{
		$lead = $this->lead;

		$credentials = array(
			'consumerKey'    => $lead->campaign->consumerKey,
			'consumerSecret' => $lead->campaign->consumerSecret,
			'accessToken'    => $lead->campaign->accessToken,
			'accessSecret'   => $lead->campaign->accessSecret,
		);
		$api = new NR_AWeber($credentials, $lead->campaign->uniquelistid);

		jimport('joomla.application.helper');

		$ad_tracking = 'convertforms_' . JApplicationHelper::stringURLSafe($lead->campaign->name);
		$name        = isset($lead->params['name']) ? $lead->params['name'] : '';
		$tags        = isset($lead->params['tags']) ? explode(',', $lead->params['tags']) : array();

		$user = array(
			'email'          => $lead->email,
			'ip_address'     => $_SERVER['REMOTE_ADDR'],
			'ad_tracking'    => $ad_tracking,
			'name'           => $name,
			'tags'           => $tags,
			'updateexisting' => $lead->campaign->updateexisting
		);

		if ($customFields = $api->validateCustomFields($lead->params))
		{
			$user['custom_fields'] = $customFields;
		}

		$api->subscribe($user);
	}

	/**
	 *  Create the final credentials with the auth code
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

		if (!is_object($article) || !isset($article->params) || !isset($article->service) || ($article->service != 'aweber'))
		{
			return;
		}

		$this->loadWrapper();
		$oldCampaign = false;

		if (isset($article->id))
		{
			$oldCampaign = ConvertForms\Helper::getCampaign($article->id);
		}

		$params = json_decode($article->params);

		if (isset($params->authcode) && !NR_AWeber::checkAuthCode($params->authcode))
		{
			JFactory::getApplication()->enqueueMessage(JText::_('PLG_CONVERTFORMS_AWEBER_WRONG_AUTH_CODE'), 'error');
			return;
		}

		if (isset($params->authcode) && !NR_AWeber::checkCredentials($params, $oldCampaign))
		{
			try {
				$credentials            = AWeberAPI::getDataFromAweberID($params->authcode);
				$params->consumerKey    = $credentials[0];
				$params->consumerSecret = $credentials[1];
				$params->accessToken    = $credentials[2];
				$params->accessSecret   = $credentials[3];
				$article->params        = json_encode($params);
				JFactory::getApplication()->enqueueMessage(JText::_('PLG_CONVERTFORMS_AWEBER_CONNECTION_ESTABLISHED'));
			}
			catch (Exception $e)
			{
				JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
			}
		}

		return true;
	}

	/**
	 *  Returns Service Wrapper File
	 *
	 *  @return  string
	 */
	protected function getWrapperFile()
	{
		return JPATH_PLUGINS . '/convertforms/aweber/wrapper/wrapper.php';
	}

}