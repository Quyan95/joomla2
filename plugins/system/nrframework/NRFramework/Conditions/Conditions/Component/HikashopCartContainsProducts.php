<?php

/**
 * @author          Tassos.gr
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2023 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

namespace NRFramework\Conditions\Conditions\Component;

defined('_JEXEC') or die;

class HikashopCartContainsProducts extends HikashopBase
{
    public function prepareSelection()
    {
		return $this->getPreparedSelection();
    }

    /**
	 *  Pass check
	 *
	 *  @return  bool
	 */
	public function pass()
	{
		return $this->passProductsInCart(['product_id', 'cart_product_parent_id'], 'cart_product_quantity');
    }
}