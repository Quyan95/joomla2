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

/**
 *  Zoho CRM Convert Forms Plugin
 *
 *  Note: Zoho CRM is using spaces in their custom field names (First Name, Last Name e.t.c).
 *  Therefore we can't apply any filtering that strips away spaces on the Field Key 
 *  option during form saving in the backend as we can end up with a broken form.
 *
 *  Commit regression: 13d8dc4
 *  https://bitbucket.org/tassosm/convertforms/commits/13d8dc475816a83c697ec81e5558d4680dfb4dcd
 */
class plgConvertFormsZohoCRM extends \ConvertForms\Plugin
{
	/**
	 *  Main method to store data to service
	 *
	 *  @return  void
	 */
	public function subscribe()
	{
		$api = new NR_ZohoCRM(array(
			'authenticationToken' => $this->lead->campaign->authenticationToken,
			'datacenter'		  => isset($this->lead->campaign->dc) ? $this->lead->campaign->dc : null
		));

		$api->subscribe(
			$this->lead->email,
			$this->lead->params,
			$this->lead->campaign->zohomodule,
			$this->lead->campaign->updateexisting,
			$this->lead->campaign->triggerworkflow,
			$this->lead->campaign->approval
		);

		if (!$api->success())
		{
			throw new Exception($api->getLastError());
		}
	}
}