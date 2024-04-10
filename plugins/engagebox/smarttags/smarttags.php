<?php

/**
 * @package         EngageBox
 * @version         6.1.4 Pro
 * 
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            http://www.tassos.gr
 * @copyright       Copyright © 2020 Tassos Marinos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Plugin\CMSPlugin;

class plgEngageBoxSmartTags extends CMSPlugin
{
    /**
     *  Replaces Smart Tags in a string
     *  
     *  We don't use the afterRender event here because it doesn't allow us to replace tags inside the Custom CSS option which is injected into the <head>.
     *  
     *  @todo Discontinue this plugin and move Smart Tags replacements within the Box::render() method.
     * 
     *  @param   string  &$box  The box instance
     *
     *  @return  void
     */
	public function onEngageBoxBeforeRender(&$box)
	{
        $box = \EngageBox\Box::replaceSmartTags($box, $box);
    }
}