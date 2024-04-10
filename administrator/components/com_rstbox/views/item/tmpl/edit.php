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

use Joomla\CMS\HTML\HTMLHelper;

HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');

$doc = JFactory::getDocument();
$doc->addScriptDeclaration('
    window.EBReloadForm = () => {
        const isJ3 = typeof Joomla.loadingLayer == "function";
        if (isJ3) {
            Joomla.loadingLayer("show");
        } else {
            document.body.appendChild(document.createElement("joomla-core-loader"));
        }
        document.querySelector("input[name=task]").value = "item.reload";
        Joomla.submitform("item.reload", document.getElementById("adminForm"));
    };
');

if (defined('nrJ4'))
{
    JHtml::_('formbehavior.chosen', '.hasChosen');
    $doc->getWebAssetManager()->useScript('webcomponent.core-loader');
} else {
    JHtml::_('formbehavior.chosen', 'select:not(.noChosen)');
    JHtml::_('behavior.tabstate');
}




$smartTagsModal =  [
    'url'        => JURI::base() . 'index.php?option=com_rstbox&view=item&layout=smarttags&tmpl=component',
    'title'      => JText::_('NR_SMARTTAGS'),
    'width'      => '800px',
    'height'     => '300px',
    'modalWidth' => '80',
    'bodyHeight' => '60',
    'footer'     => '<a type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal" aria-hidden="true">'. JText::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</a>'
];

echo JHtml::_('bootstrap.renderModal','smarttags', $smartTagsModal);


function tabSetStart()
{
    echo defined('nrJ4') ? HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'general', 'recall' => true]) : JHtml::_('bootstrap.startTabSet', 'myTab', ['active' => 'general']);;
}

function tabSetEnd()
{
    echo defined('nrJ4') ? HTMLHelper::_('uitab.endTabSet') : JHtml::_('bootstrap.endTabSet');;
}

function tabStart($name, $title)
{
    echo defined('nrJ4') ? HTMLHelper::_('uitab.addTab', 'myTab', $name, JText::_($title)) : JHtml::_('bootstrap.addTab', 'myTab', $name, JText::_($title));
}

function tabEnd()
{
    echo defined('nrJ4') ? HTMLHelper::_('uitab.endTab') : JHtml::_('bootstrap.endTab');
}

if (defined('nrJ4'))
{
    NRFramework\HTML::fixFieldTooltips();
}

?>

<!-- Test Mode Notice -->
<?php if ($this->item->testmode) { ?>
	<div class="alert alert-warning text-center">
		<?php echo JText::_('COM_RSTBOX_ITEM_TESTMODE_NOTICE'); ?>
	</div>
<?php } ?>

<div class="rstbox rstbox-item form-horizontal">
    <form action="<?php echo JRoute::_('index.php?option=com_rstbox&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="adminForm">
        <div class="<?php echo defined('nrJ4') ? 'row' : 'row-fluid' ?>">
            <div class="span9 col-md-9">
                <div class="rowTop mt-2 mb-4">
                    <?php echo $this->form->renderFieldset("top") ?>
                </div>

                <?php tabSetStart() ?>

                <!-- Content Tab -->
                <?php tabStart('general', 'COM_RSTBOX_CONTENT'); ?>
                <div class="boxtype <?php echo $this->form->getData()->get('boxtype') ?>">
                    <?php echo $this->form->renderFieldset($this->form->getData()->get('boxtype')); ?>
                </div>
                <?php tabEnd() ?>

                <!-- Appearance Tab -->
                <?php tabStart('appearance', 'COM_RSTBOX_APPEARANCE'); ?>
                    <?php echo $this->form->renderFieldset("appearance") ?>
                <?php tabEnd() ?>

                <!-- Trigger Tab -->
                <?php tabStart('trigger', 'COM_RSTBOX_BOX_BEHAVIOR'); ?>
                <div class="<?php echo defined('nrJ4') ? 'row' : 'row-fluid' ?>">
                    <?php echo $this->form->renderFieldset("items") ?>
                </div>
                <?php tabEnd() ?>

                <!-- Publishing Assignments Tab -->
                <?php 
                    tabStart('displayConditions', 'NR_PUBLISHING_ASSIGNMENTS');
                    echo $this->form->renderFieldSet('display_conditions');
                    tabEnd();
                ?>

                <!-- Params -->

                <?php 
                    $fieldSets = $this->form->getFieldsets();

                    foreach ($fieldSets as $name => $fieldset)
                    {
                        if (!isset($fieldset->tag) || $fieldset->tag != 'params')
                        {
                            continue;
                        }

                        tabStart($fieldset->name, $fieldset->label);

                        if ($fieldset->description)
                        {
                            echo '
                                <div class="fieldset-desc alert alert-info">
                                    <span>' . JText::_($fieldset->description) . '</span>
                                    <a href="' . $fieldset->help . '" target="_blank"><span class="icon icon-help"></span> ' . JText::_("JHELP") . '</a>
                                </div>
                                ';
                        }

                        echo $this->form->renderFieldset($fieldset->name);
                        tabEnd();
                    }
                ?>

                <!-- Advanced Tab -->
                <?php
                    tabStart('advanced', 'NR_ADVANCED');
                    echo $this->form->renderFieldset('advanced');
                    tabEnd();
                ?>

                <input type="hidden" name="task" value="item.edit" />
                <?php echo JHtml::_('form.token'); ?>
                <?php tabSetEnd() ?>
            </div>

            <div class="span3 col-md-3 px-4 form-vertical">
                <?php echo $this->form->renderFieldset('general') ?>
            </div>
        </div>

        <?php 
            // Render all fieldsets supposed to be displayed in a popup
            $fieldSets = $this->form->getFieldsets();

            foreach ($fieldSets as $name => $fieldset)
            {
                if (!isset($fieldset->popup))
                {
                    continue;
                }
                
                $content = '<div style="padding:1rem">' . $this->form->renderFieldset($fieldset->name) . '</div>';

                $modal =  \JHtml::_('bootstrap.renderModal', $fieldset->name, [
                    'title' => JText::_($fieldset->label),
                    'closeButton' => true,
                    'footer' => '<a type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal" aria-hidden="true">Close</a>'
                ], $content);

                // Add a unique CSS class to fix some styling issues on J3
                echo str_replace('modal hide', 'modal hide popup_settings_modal', $modal);
            }
        ?>

    </form>
</div>