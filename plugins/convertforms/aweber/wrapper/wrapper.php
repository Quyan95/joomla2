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
defined('_JEXEC') or die;

require_once __DIR__ . '/aweber_api.php';

class NR_AWeber
{
	/**
	 *  Create a new instance of NR_AWeber
	 *
	 *  @param  array  	$credentials  An array containing the consumerKey, consumerSecret,
	 *                                accessToken and accessSecret
	 *  @param  string  $listUID      The AWeber Unique List ID
	 */
	public function __construct($credentials, $listUID)
	{
		if (!NR_AWeber::checkCredentials($credentials))
		{
			throw new Exception("The AWeber Credentials are incomplete or incorrect", 1);
		}

		$this->application = new AWeberAPI($credentials['consumerKey'], $credentials['consumerSecret']);
		$this->account     = $this->application->getAccount($credentials['accessToken'], $credentials['accessSecret']);
		$this->list        = $this->findList($listUID);
		if ($this->list === false)
		{
			throw new Exception("The AWeber List could not be found", 1);
		}

	}

	/**
	 *  Finds the List resource
	 *
	 *  @param   string  $listUID  The AWeber Unique List ID
	 *
	 *  @return  object            The AWeber List resource
	 */
	public function findList($listUID)
	{
		$foundLists = $this->account->lists->find(array('name' => $listUID));
		if (count($foundLists))
		{
			$foundList = $foundLists[0];
			$listUrl   = "/accounts/{$this->account->id}/lists/{$foundList->id}";
			$list      = $this->account->loadFromUrl($listUrl);
			return $list;
		}
		return false;
	}

	/**
	 *  Finds the Subscriber resource
	 *
	 *  @param   string  $email  The email of the subscriber we are searching for
	 *
	 *  @return  object          The AWeber Subscriber resource
	 */
	public function findSubscriber($email)
	{
		$foundSubscribers = $this->list->subscribers->find(array('email' => $email));
		return $foundSubscribers[0];
	}

	/**
	 *  Updates a Subscriber
	 *
	 *  @param   array   $subscriber  An array containing subscriber data
	 *
	 *  @return  boolean              The result of the operation
	 */
	public function updateSubscriber($subscriber)
	{
		$oldSubscriber = $this->findSubscriber($subscriber['email']);

		// If the subscriber does not exist, create it
		if (!is_object($oldSubscriber))
		{
			return $this->createSubscriber($subscriber);
		}

		foreach ($subscriber as $key => $value)
		{
			// Fix Tags
			if ($key == 'tags')
			{
				$newTags = $value;

				if (empty($newTags) || is_null($newTags))
				{
					continue;
				}

				$oldSubscriber->tags = [
					'add'    => (array) $newTags,
					'remove' => array_diff($oldSubscriber->data['tags'], $newTags)
				];

				continue;
			}

			$oldSubscriber->$key = $value;
		}

		$oldSubscriber->save();
		return true;
	}

	/**
	 *  Creates a Subscriber
	 *
	 *  @param   array   $subscriber  An array containing subscriber data
	 *
	 *  @return  boolean              The result of the operation
	 */
	public function createSubscriber($subscriber)
	{
		$newSubscriber = $this->list->subscribers->create($subscriber);
		return true;
	}

	/**
	 *  The entry point of a subscribe operation
	 *
	 *  @param   array   $subscriber  An array containing subscriber data
	 *
	 *  @return  boolean              The result of the operation
	 */
	public function subscribe($subscriber)
	{
		if ($subscriber['updateexisting'])
		{
			$this->updateSubscriber($subscriber);
		}
		else
		{
			$this->createSubscriber($subscriber);
		}
		return true;
	}

	/**
	 *  Checks if the credentials have the correct structure for the OAuth 1.0 protocol
	 *
	 *  @param   array    $credentials    An array containing the consumerKey, consumerSecret,
	 *                                    accessToken and accessSecret
	 *  @param 	 object   $oldCampaign    The campaign object carrying the old data
	 *
	 *  @return  boolean                  The result of the check
	 */
	public static function checkCredentials($credentials, $oldCampaign = false)
	{
		// Typecast simple objects
		$credentials = (array) $credentials;

		// Remove empty values
		$credentials = array_filter($credentials);

		// We need to have the following keys present in the credentials array
		$requiredCredentialsKeys = array('consumerKey', 'consumerSecret', 'accessToken', 'accessSecret');

		// We need to check if the Auth Code has changed
		if (($oldCampaign !== false)
			&& (is_object($oldCampaign))
			&& (isset($oldCampaign->authcode))
			&& ($oldCampaign->authcode !== $credentials['authcode']))
		{
			return false;
		}

		// Check if the above mandatory keys are present
		if (count(array_intersect_key(array_flip($requiredCredentialsKeys), $credentials)) == count($requiredCredentialsKeys))
		{
			return true;
		}
	}

	/**
	 *  Returns a new array with valid only custom fields
	 *
	 *  @param   array  $customFields   Array of custom fields
	 *
	 *  @return  array  				Array of valid only custom fields
	 */
	public function validateCustomFields($customFields)
	{
		$fields = array();

		if (!is_array($customFields))
		{
			return $fields;
		}

		$listCustomFields = $this->list->custom_fields;
		if (count($listCustomFields))
		{
			foreach ($listCustomFields as $key => $customField)
			{
				if (!isset($customFields[$customField->name]))
				{
					continue;
				}

				$fields[$customField->name] = $customFields[$customField->name];
			}
		}

		return $fields;
	}

	/**
	 *  Checks if the Authorization Code structure is correct
	 *
	 *  @param   string  $authCode  The Authorization Code
	 *
	 *  @return  boolean            The result of the check
	 */
	public static function checkAuthCode($authCode)
	{
		$values = explode('|', $authCode);

		if (count($values) < 5)
		{
			return false;
		}

		return true;
	}
}