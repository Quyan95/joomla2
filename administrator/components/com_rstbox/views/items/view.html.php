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
 
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

// import Joomla view library
jimport('joomla.application.component.view');
 
/**
 * Items View
 */
class RstboxViewItems extends JViewLegacy
{
    /**
     * Items view display method
     * 
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     * 
     * @return  mixed  A string if successful, otherwise a JError object.
     */
    function display($tpl = null) 
    {
        $this->items         = $this->get('Items');
        $this->state         = $this->get('State');
        $this->pagination    = $this->get('Pagination');
        $this->filterForm    = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');
        $this->config        = EngageBox\Helper::getParams();

        // Check for errors.
        if (count($errors = $this->get('Errors'))) 
        {
            JFactory::getApplication()->enqueueMessage($errors, 'error');
            return false;
        }

        // Set the toolbar
        $this->addToolBar();

        // Display the template
        parent::display($tpl);
    }

    /**
     *  Add Toolbar to layout
     */
    protected function addToolBar() 
    {
        $canDo = EngageBox\Admin::getActions();
        $state = $this->get('State');
        $viewLayout = JFactory::getApplication()->input->get('layout', 'default');

        // Joomla J4
        if (defined('nrJ4'))
        {
            $toolbar = Toolbar::getInstance('toolbar');

            if ($viewLayout == 'import')
            {
                JFactory::getDocument()->setTitle(JText::_('RSTBOX') . ': ' . JText::_('COM_RSTBOX_IMPORT_POPUP'));
                JToolbarHelper::title(JText::_('RSTBOX') . ': ' . JText::_('COM_RSTBOX_IMPORT_POPUP'));
                JToolbarHelper::back();
            }
            else
            {
                ToolbarHelper::title(JText::_('RSTBOX'));
                
                if ($canDo->get('core.create'))
                {
                    $newGroup = $toolbar->dropdownButton('new-group')
                        ->text('NR_NEW')
                        ->toggleSplit(false)
                        ->icon('fas fa-plus')
                        ->buttonClass('btn btn-action');

                    $newGroup->configure(
                        function (Toolbar $childBar)
                        {
                            $childBar->popupButton('new')->text('NR_NEW')->selector('ebSelectTemplate')->icon('icon-new')->buttonClass('btn btn-success');
                            $childBar->addNew('item.add')->text('COM_RSTBOX_BLANK_POPUP');
                            $childBar->standardButton('import')->text('NR_IMPORT')->task('items.import')->icon('icon-upload');
                        }
                    );
                }

                $dropdown = $toolbar->dropdownButton('status-group')
                    ->text('JTOOLBAR_CHANGE_STATUS')
                    ->toggleSplit(false)
                    ->icon('fas fa-ellipsis-h')
                    ->buttonClass('btn btn-action')
                    ->listCheck(true);

                $childBar = $dropdown->getChildToolbar();
                
                if ($canDo->get('core.edit.state'))
                {
                    $childBar->publish('items.publish')->listCheck(true);
                    $childBar->unpublish('items.unpublish')->listCheck(true);
                    $childBar->standardButton('copy')->text('JTOOLBAR_DUPLICATE')->task('items.copy')->listCheck(true);
                    $childBar->standardButton('export')->text('NR_EXPORT')->task('items.export')->icon('icon-download')->listCheck(true);
                    $childBar->standardButton('refresh')->text('COM_RSTBOX_RESET_STATISTICS')->task('items.reset')->listCheck(true);
                    $childBar->standardButton('removecookie')->text('COM_RSTBOX_REMOVE_COOKIE')->task('items.removeCookie')->icon('icon-minus-circle')->listCheck(true);
                    $childBar->trash('items.trash')->listCheck(true);
                }

                if ($this->state->get('filter.state') == -2)
                {
                    $toolbar->delete('items.delete')
                        ->text('JTOOLBAR_EMPTY_TRASH')
                        ->message('JGLOBAL_CONFIRM_DELETE')
                        ->listCheck(true);
                }

                if ($canDo->get('core.admin'))
                {
                    $toolbar->preferences('com_rstbox');
                }

		        $toolbar->help('JHELP', false, "http://www.tassos.gr/joomla-extensions/responsive-scroll-triggered-box-for-joomla/docs");
            }

            return;
        }

        // Joomla 3
        if ($viewLayout == 'import')
        {
            JFactory::getDocument()->setTitle(JText::_('RSTBOX') . ': ' . JText::_('COM_RSTBOX_IMPORT_POPUP'));
            JToolbarHelper::title(JText::_('RSTBOX') . ': ' . JText::_('COM_RSTBOX_IMPORT_POPUP'));
            JToolbarHelper::back();
        }
        else
        {
            JToolBarHelper::title(JText::_('RSTBOX'));

            if ($canDo->get('core.create'))
            {
                JToolbarHelper::addNew('item.add');
            }
            
            if ($canDo->get('core.edit'))
            {
                JToolbarHelper::editList('item.edit');
            }

            if ($canDo->get('core.create'))
            {
                JToolbarHelper::custom('items.copy', 'copy', 'copy', 'JTOOLBAR_DUPLICATE', true);
            }

            if ($canDo->get('core.edit.state') && $state->get('filter.state') != 2)
            {
                JToolbarHelper::publish('items.publish', 'JTOOLBAR_PUBLISH', true);
                JToolbarHelper::unpublish('items.unpublish', 'JTOOLBAR_UNPUBLISH', true);
            }

            if ($canDo->get('core.delete') && $state->get('filter.state') == -2)
            {
                JToolbarHelper::deleteList('', 'items.delete', 'JTOOLBAR_EMPTY_TRASH');
            }
            else if ($canDo->get('core.edit.state'))
            {
                JToolbarHelper::trash('items.trash');
            }

            if ($canDo->get('core.create'))
            {
                JToolbarHelper::custom('items.export', 'box-remove', 'box-remove', 'NR_EXPORT');
                JToolbarHelper::custom('items.import', 'box-add', 'box-add', 'NR_IMPORT', false);
            }

            JToolbarHelper::custom('items.reset', 'refresh', 'box-reset', 'COM_RSTBOX_RESET_STATISTICS');

            if ($canDo->get('core.admin'))
            {
                JToolbarHelper::preferences('com_rstbox');
            }
        }

        JToolbarHelper::help("Help", false, "http://www.tassos.gr/joomla-extensions/responsive-scroll-triggered-box-for-joomla/docs");
    }
}