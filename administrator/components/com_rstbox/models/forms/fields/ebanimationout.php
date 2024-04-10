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

class JFormFieldEBAnimationOut extends JFormFieldGroupedList
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
			'Classic' => [
				'transition.fadeOut' => 'fadeOut',
				'transition.swoopOut' => 'swoopOut',
				'transition.whirlOut' => 'whirlOut',
				'transition.shrinkOut' => 'shrinkOut',
				'transition.expandOut' => 'expandOut',
			],
			
			'Flip' => [
				'transition.flipXOut' => 'flipXOut',
				'transition.flipYOut' => 'flipYOut',
				'transition.flipBounceXOut' => 'flipBounceXOut',
				'transition.flipBounceYOut' => 'flipBounceYOut',
			],
			'Bounce' => [
				'transition.bounceOut' => 'bounceOut',
				'transition.bounceUpOut' => 'bounceUpOut',
				'transition.bounceDownOut' => 'bounceDownOut',
				'transition.bounceLeftOut' => 'bounceLeftOut',
				'transition.bounceRightOut' => 'bounceRightOut',
			],
			'Slide' => [
				'slideUp' => 'slideOut',
				'rstbox.slideUpOut' => 'slideUpOut',
				'rstbox.slideDownOut' => 'slideDownOut',
				'rstbox.slideLeftOut' => 'slideLeftOut',
				'rstbox.slideRightOut' => 'slideRightOut',
				'transition.slideUpOut' => 'slideFadeUpOut',
				'transition.slideDownOut' => 'slideFadeDownOut',
				'transition.slideLeftOut' => 'slideFadeLeftOut',
				'transition.slideRightOut' => 'slideFadeRightOut',
				'transition.slideUpBigOut' => 'slideUpBigOut',
				'transition.slideDownBigOut' => 'slideDownBigOut',
				'transition.slideLeftBigOut' => 'slideLeftBigOut',
				'transition.slideRightBigOut' => 'slideRightBigOut',
			],
			'Perspective' => [
				'transition.perspectiveUpOut' => 'perspectiveUpOut',
				'transition.perspectiveDownOut' => 'perspectiveDownOut',
				'transition.perspectiveLeftOut' => 'perspectiveLeftOut',
				'transition.perspectiveRightOut' => 'perspectiveRightOut',
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