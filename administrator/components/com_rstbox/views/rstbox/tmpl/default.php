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

defined('_JEXEC') or die('Restricted access');

$license = EngageBox\Helper::licenseIsValid();
$docHome = 'http://www.tassos.gr/joomla-extensions/engagebox/';

// Load the EngageBox Templates Library
new EngageBox\Library();

// Display extension notices
\NRFramework\Notices\Notices::getInstance([
	'ext_element' => 'com_rstbox',
	'ext_xml' => 'com_rstbox'
])->show();
?>
<div class="<?php echo defined('nrJ4') ? 'row' : 'row-fluid' ?>">
	<span class="span8 col-md-8">
		<div class="clearfix">
			<ul class="nr-icons clearfix">
				<li>
					<a class="tf-open-library-modal" data-bs-toggle="modal" data-toggle="modal" data-bs-target="#ebSelectTemplate" href="#ebSelectTemplate">
						<span class="nr-img icon-new"></span>
						<span><?php echo JText::_("NR_NEW") ?></span>
					</a>
				</li>
				<li>
					<a href="<?php echo JURI::base() ?>index.php?option=com_rstbox&view=items">
						<span class="nr-img icon-list"></span>
						<span><?php echo JText::_("NR_LIST") ?></span>
					</a>
				</li>
				<li>
					<a href="<?php echo JURI::base() ?>index.php?option=com_rstbox&view=items&layout=import">
						<span class="nr-img icon-box-add"></span>
						<span><?php echo JText::_("NR_IMPORT") ?></span>
					</a>
				</li>
				<li>
					<a href="<?php echo JURI::base() ?>index.php?option=com_config&view=component&component=com_rstbox&path=&return=<?php echo MD5(JURI::base()."index.php?option=com_rstbox") ?>">
						<span class="nr-img icon-options"></span>
						<span><?php echo JText::_("JOPTIONS") ?></span>
					</a>
				</li>
				<li>
					<a href="<?php echo $docHome; ?>docs" target="_blank">
						<span class="nr-img icon-info"></span>
						<span><?php echo JText::_("NR_DOCUMENTATION")?></span>
					</a>
				</li>
			</ul>
		</div>
	</span>
	<span class="span4 col-md-4">
		<?php echo JHtml::_('bootstrap.startAccordion', "info", array('active' => 'slide0')); ?>
		
		<!-- Information Slide -->
		<?php echo JHtml::_('bootstrap.addSlide', "info", JText::_("NR_INFORMATION"), 'slide0'); ?>
		<table class="table table-striped">
			<tbody>
				<tr>
					<td><?php echo JText::_("NR_EXTENSION"); ?></td>
					<td><?php echo JText::_("COM_RSTBOX"); ?></td>
				</tr>	
				<tr>
					<td><?php echo JText::_("NR_VERSION"); ?></td>
					<td><?php echo NRFramework\Functions::getExtensionVersion("com_rstbox", true); ?>
						<a href="http://www.tassos.gr/joomla-extensions/engagebox/changelog" target="_blank"><?php echo JText::_("NR_CHANGELOG"); ?></a>
					</td>
				</tr>
				<tr>
					<td><?php echo JText::_("NR_DOWNLOAD_KEY"); ?></td>
					<td>
						<?php if ($license) { ?>
						<span class="badge bg-success label-success"><?php echo JText::_("NR_OK"); ?></span>
						<?php } else { ?>
						<span class="badge bg-danger label-important"><?php echo JText::_("NR_MISSING"); ?></span>
						<?php } ?>
					</td>
				</tr>
				<tr>
					<td><?php echo JText::_("NR_LICENSE"); ?></td>
					<td><a href="http://www.tassos.gr/license" target="_blank">GNU GPLv3 Commercial</td>
				</tr>
				<tr>
					<td><?php echo JText::_("NR_AUTHOR"); ?></td>
					<td>Tassos Marinos - <a href="http://www.tassos.gr" target="_blank">www.tassos.gr</td>
				</tr>
				<tr>
					<td><?php echo JText::_("NR_FOLLOWME"); ?></td>
					<td><a href="#" onclick="window.open('https://twitter.com/intent/follow?screen_name=tassosm','tassos.gr','width=500,height=500');"><span class="label label-info"><?php echo JText::sprintf("NR_FOLLOW", "@mtassos") ?></span></a></td>
				</tr>
			</tbody>
		</table>

		<?php echo JHtml::_('bootstrap.endSlide'); ?>

		<!-- Translations Slide -->
		<?php echo JHtml::_('bootstrap.addSlide', "info", JText::_("NR_HELP_WITH_TRANSLATIONS"), 'slide3'); ?>
		<p>
			<?php echo JText::sprintf("NR_TRANSLATE_INTEREST", JText::_("COM_RSTBOX")); ?>
			<a href="https://www.transifex.com/tassosgr/engagebox/" target="_blank"><?php echo JText::_("NR_TRANSIFEX_REQUEST") ?></a>.
		</p>
		<?php echo JHtml::_('bootstrap.endSlide'); ?>

		<?php echo JHtml::_('bootstrap.endAccordion'); ?>
	</span>
</div>
<hr>
<?php include_once(JPATH_COMPONENT_ADMINISTRATOR."/layouts/footer.php"); ?>