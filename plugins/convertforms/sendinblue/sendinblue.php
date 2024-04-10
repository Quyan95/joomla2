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

class plgConvertFormsSendInBlue extends \ConvertForms\Plugin
{
	/**
	 *  Main method to store data to service
	 *
	 *  @return  void
	 */
	public function subscribe()
	{
		$class_name = $this->getCampaignIntegration($this->lead->campaign);

		$api = new $class_name([
			'api' => $this->lead->campaign->api
		]);
		$api->subscribe(
			$this->lead->email,
			$this->lead->params,
			$this->lead->campaign->list,
			(bool) $this->lead->campaign->updateexisting
		);
		
		if (!$api->success())
		{
			throw new Exception($api->getLastError());
		}
	}

    /**
     * Returns the campaign integration.
	 * Loads the exact version we have specified in the campaign settings.
     * 
     * @param   array   $campaignData
     * 
     * @return  string
     */
	protected function getCampaignIntegration($campaignData)
	{
		$campaignData = (array) $campaignData;
		
		return parent::getCampaignIntegration($campaignData) . $this->getSuffix($campaignData['version']);
	}

	/**
	 * Get integration suffix
	 * 
	 * @param   string  $version
	 * 
	 * @return  mixed
	 */
	private function getSuffix($version)
	{
		return (int) $version == 3 ? 3 : '';
	}
}