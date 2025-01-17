<?php

/**
 * @package         EngageBox
 * @version         6.1.4 Pro
 * 
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2023 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

defined('_JEXEC') or die('Restricted access');

extract($displayData);

if (!$readonly && !$disabled)
{
    JHtml::stylesheet('https://cdn.jsdelivr.net/npm/glightbox@3.1.0/dist/css/glightbox.min.css');
    JHtml::script('https://cdn.jsdelivr.net/gh/mcstudios/glightbox@3.1.0/dist/js/glightbox.min.js');
}

?>