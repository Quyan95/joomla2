<?php

/**
 * @package         EngageBox
 * @version         6.1.4 Pro
 * 
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            http://www.tassos.gr
 * @copyright       Copyright © 2019 Tassos Marinos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

defined('_JEXEC') or die;

require_once __DIR__ . '/script.install.helper.php';

class Com_RstBoxInstallerScript extends Com_RstBoxInstallerScriptHelper
{
	public $name = 'RSTBOX';
	public $alias = 'rstbox';
	public $extension_type = 'component';

	public function onAfterInstall()
	{
		if ($this->install_type == 'update') 
		{
			require_once __DIR__ . '/autoload.php';

			try {
				(new EngageBox\Migrator($this->installedVersion))->start();
			} catch (\Throwable $th)
			{
			}
        }
    }
}
