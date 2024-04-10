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

class plgConvertFormsActiveCampaign extends \ConvertForms\Plugin
{
	/**
	 *  Main method to store data to service
	 *
	 *  @return  void
	 */
	function subscribe()
	{
		// Call API
		$api = new NR_ActiveCampaign(array(
			'api' 	   => $this->lead->campaign->api, 
			'endpoint' => $this->lead->campaign->endpoint
		));

		// Subscribe
		$api->subscribe(
			$this->lead->email,
			isset($this->lead->params['name']) ? $this->lead->params['name'] : '',
			$this->lead->campaign->list,
			isset($this->lead->params['tags']) ? $this->lead->params['tags'] : '',
			$this->lead->params,
			isset($this->lead->campaign->updateexisting) ? $this->lead->campaign->updateexisting : true
		);
		
		if (!$api->success())
		{
			throw new Exception($api->getLastError());
		}
	}
}