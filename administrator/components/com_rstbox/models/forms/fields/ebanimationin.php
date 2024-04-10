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

JFormHelper::loadFieldClass('groupedlist');

class JFormFieldEBAnimationIn extends JFormFieldGroupedList
{
    protected $layout = 'joomla.form.field.groupedlist-fancy-select';

    /**
     * Method to get a list of options for a list input.
     *
     * @return      array           An array of JHtml options.
     */
    protected function getGroups()
    {
        $data = [
			
			'Attention' => [
				'callout.bounce' => 'Bounce',
				'callout.shake' => 'Shake',
				'callout.flash' => 'Flash',
				'callout.pulse' => 'Pulse',
				'callout.swing' => 'Swing',
				'callout.tada' => 'Tada',
			],
			
			'Classic' => [
				'transition.fadeIn' => 'fadeIn',
				'transition.swoopIn' => 'swoopIn',
				'transition.whirlIn' => 'whirlIn',
				'transition.shrinkIn' => 'shrinkIn',
				'transition.expandIn' => 'expandIn',
			],
			
			'Flip' => [
				'transition.flipXIn' => 'flipXIn',
				'transition.flipYIn' => 'flipYIn',
				'transition.flipBounceXIn' => 'flipBounceXIn',
				'transition.flipBounceYIn' => 'flipBounceYIn',
			],
			'Bounce' => [
				'transition.bounceIn' => 'bounceIn',
				'transition.bounceUpIn' => 'bounceUpIn',
				'transition.bounceDownIn' => 'bounceDownIn',
				'transition.bounceLeftIn' => 'bounceLeftIn',
				'transition.bounceRightIn' => 'bounceRightIn',
			],
			'Slide' => [
				'slideDown' => 'slideIn',
				'rstbox.slideUpIn' => 'slideUpIn',
				'rstbox.slideDownIn' => 'slideDownIn',
				'rstbox.slideLeftIn' => 'slideLeftIn',
				'rstbox.slideRightIn' => 'slideRightIn',
				'transition.slideUpIn' => 'slideFadeUpIn',
				'transition.slideDownIn' => 'slideFadeDownIn',
				'transition.slideLeftIn' => 'slideFadeLeftIn',
				'transition.slideRightIn' => 'slideFadeRightIn',
				'transition.slideUpBigIn' => 'slideUpBigIn',
				'transition.slideDownBigIn' => 'slideDownBigIn',
				'transition.slideLeftBigIn' => 'slideLeftBigIn',
				'transition.slideRightBigIn' => 'slideRightBigIn',
			],
			'Perspective' => [
				'transition.perspectiveUpIn' => 'perspectiveUpIn',
				'transition.perspectiveDownIn' => 'perspectiveDownIn',
				'transition.perspectiveLeftIn' => 'perspectiveLeftIn',
				'transition.perspectiveRightIn' => 'perspectiveRightIn',
			]
			
		];

        $groups = [];

        foreach ($data as $groupKey => $value)
        {
            foreach ($value as $_key => $_value)
			{
				$groupLabel = '';
				
				$groupLabel = JText::_($groupKey);
				
				
                $groups[$groupLabel][] = JHTML::_('select.option', $_key, $_value);
			}
        }

        return $groups;
    }
	
    protected function getInput()
    {
		$class = (string) $this->element['class'];

		$search_placeholder = (string) $this->element['search_placeholder'];
		if ($search_placeholder)
		{
			$this->class = $class . '" search-placeholder="' . JText::_($search_placeholder);
		}
		else
		{
			$this->class = $class;
		}

		return parent::getInput();
	}
}