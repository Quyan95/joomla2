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

namespace EngageBox;

use Joomla\Registry\Registry;

defined('_JEXEC') or die('Restricted access');

/**
 * EngageBox Migrator Class helps us fix and prevent backward compatibility issues between release updates.
 */
class Migrator
{
    /**
     * The database class
     *
     * @var object
     */
    private $db;

    /**
     * Indicates the current installed version of the extension
     *
     * @var string
     */
    private $installedVersion;
    
    /**
     * Class constructor
     *
     * @param string $installedVersion  The current extension version
     */
    public function __construct($installedVersion, $dbSource = null, $dbDestination = null)
    {
        $this->db = $dbSource ? $dbSource : \JFactory::getDbo();
        $this->dbDestination = $dbDestination ? $dbDestination : \JFactory::getDbo();
        $this->installedVersion = $installedVersion;
    }
    
    /**
     * Start the migration process
     *
     * @return void
     */
    public function start()
    {
        $this->removeTemplatesData();
       
        if (!$data = $this->getBoxes())
        {
            return;
        }

        foreach ($data as $key => $box)
        {
            $box->params = new Registry($box->params);

            if (version_compare($this->installedVersion, '4.0.0', '<=')) 
            {
                $this->moveCloseOpenedBoxesOptionToActions($box);
                $this->moveAutoCloseTimerToActions($box);
                $this->fixCustomCSS($box);
                $this->fixCloseButton($box);
            }
            
            if (version_compare($this->installedVersion, '5.0.1', '<=')) 
            {
                // Pass only params
                \NRFramework\Conditions\Migrator::run($box->params);
            }
            
            if (version_compare($this->installedVersion, '6.1.2', '<='))
            {
                $this->updateEcommerceConditions($box->params);
            }
            
            // Update box using id as the primary key.
            $box->params = json_encode($box->params);

            $this->dbDestination->updateObject('#__rstbox', $box, 'id');
        }
    }

    /**
     * Get all boxes from the database
     *
     * @return array
     */
    private function getBoxes()
    {
        $db = $this->db;
    
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__rstbox');
        
        $db->setQuery($query);
    
        return $db->loadObjectList();
    }

    private function fixCloseButton(&$box)
    {
        if (!$box->params->offsetExists('closebutton.hide'))
        {
            return;
        }

        $close_button_value = $box->params->get('closebutton.hide', false) ? 0 : 1;
        $box->params->set('closebutton.show', $close_button_value);
        $box->params->remove('closebutton.hide');
    }

    private function fixCustomCSS(&$box)
    {
        if (!$css = $box->params->get('customcss'))
        {
            return;
        }

        $replacements = [
            '.rstbox-heading' => '.eb-header', // rstbox-heading no longer exist
            '.rstboxes' => '',
            '.rstbox-'  => '.eb-',
            '.rstbox_'  => '.eb-',
            '#rstbox_' . $box->id  => '.eb-' . $box->id . ' .eb-dialog'
        ];
        
        $css_new = str_ireplace(array_keys($replacements), array_values($replacements), $css);
        
        if ($css == $css_new)
        {
            return;
        }

        $box->params->set('customcss', $css_new);
    }

    private function moveCloseOpenedBoxesOptionToActions(&$box)
    {
        if (!$box->params->get('closeopened', false))
        {
            return;
        }   

        $actions = (array) $box->params->get('actions', []);

        $actions[] = [
            'enabled' => true,
            'when'    => 'open',
            'do'      => 'closeall'
        ];

        $box->params->set('actions', $actions);
        $box->params->remove('closeopened');
    }

    private function moveAutoCloseTimerToActions(&$box)
    {
        if (!$box->params->get('autoclose', false))
        {
            return;
        }

        if ((int) $box->params->get('autoclosevalue', 0) == 0)
        {
            return;
        }

        $actions = (array) $box->params->get('actions', []);

        $actions[] = [
            'enabled' => true,
            'when'    => 'afterOpen',
            'do'      => 'closebox',
            'box'     => $box->id,
            'delay'   => $box->params->get('autoclosevalue', 0) / 1000 // Convert ms to sec.
        ];

        $box->params->set('actions', $actions);
        $box->params->remove('autoclose');
    }

    /**
     * Updates eCommerce Conditions:
     * 
     * CartContainsProducts
     * CartContainsXProducts
     * CartValue
     * 
     * @param   object  $params
     * 
     * @return  void
     */
    private function updateEcommerceConditions(&$params)
    {
        $this->updateCartContainsProductsCondition($params);
        $this->updateCartContainsXProductsCondition($params);
        $this->updateCartValueCondition($params);
    }

    /**
     * Migrates value of CartContainsProducts Condition for both Hikashop & VirtueMart.
     * 
     * @param   object  $param
     * 
     * @return  void
     */
    private function updateCartContainsProductsCondition(&$params)
    {
        if (!$rules = $params->get('rules', []))
        {
            return;
        }

        /**
         * $rules may be either a string or an array of objects.
         * For this reason, we transform all cases to arrays.
         */
        if (is_array($rules))
        {
            $rules = json_encode($rules);
        }
        
        if (!$rules = json_decode($rules, true))
        {
            return;
        }

        $allowed_rules = [
            'Component\HikashopCartContainsProducts',
            'Component\VirtueMartCartContainsProducts'
        ];

        $changed = false;

        foreach ($rules as &$ruleset)
        {
            if (!isset($ruleset['rules']))
            {
                continue;
            }
            
            foreach ($ruleset['rules'] as &$rule)
            {
                if (!isset($rule['name']))
                {
                    continue;
                }
                
                if (!in_array($rule['name'], $allowed_rules))
                {
                    continue;
                }

                if (!isset($rule['value']))
                {
                    continue;
                }

                if (!is_array($rule['value']))
                {
                    continue;
                }

                if (!count($rule['value']))
                {
                    continue;
                }

                $changed = true;

                $newValue = [];
                foreach ($rule['value'] as $index => $id)
                {
                    $key = 'value' . $index;
                    
                    // Old value wasn't an array, skip if new value was found
                    if (is_array($id))
                    {
                        $newValue[$key] = $id;
                    }
                    else
                    {
                        $newValue[$key] = [
                            'value' => $id,
                            'params' => [
                                'operator' => 'any',
                                'value' => '1',
                                'value2' => '1'
                            ]
                        ];
                    }
                }
                
                $rule['value'] = $newValue;
            }
        }

        if ($changed)
        {
            $params->set('rules', $rules);
        }
    }

    /**
     * Migrates value of CartContainsXProducts Condition for both Hikashop & VirtueMart.
     * 
     * @param   object  $param
     * 
     * @return  void
     */
    private function updateCartContainsXProductsCondition(&$params)
    {
        if (!$rules = $params->get('rules', []))
        {
            return;
        }

        /**
         * $rules may be either a string or an array of objects.
         * For this reason, we transform all cases to arrays.
         */
        if (is_array($rules))
        {
            $rules = json_encode($rules);
        }
        
        if (!$rules = json_decode($rules, true))
        {
            return;
        }

        $allowed_rules = [
            'Component\HikashopCartContainsXProducts',
            'Component\VirtueMartCartContainsXProducts'
        ];

        $changed = false;

        foreach ($rules as &$ruleset)
        {
            if (!isset($ruleset['rules']))
            {
                continue;
            }
            
            foreach ($ruleset['rules'] as &$rule)
            {
                
                if (!isset($rule['name']))
                {
                    continue;
                }
                
                if (!in_array($rule['name'], $allowed_rules))
                {
                    continue;
                }

                if (!isset($rule['value']))
                {
                    continue;
                }

                if (!is_scalar($rule['value']))
                {
                    continue;
                }

                // Abort if params property is set
                if (isset($rule['params']) && is_array($rule['params']) && count($rule['params']))
                {
                    continue;
                }

                $rule['params'] = [
                    'operator' => $rule['operator']
                ];

                if (isset($rule['operator']))
                {
                    unset($rule['operator']);
                }
                
                $changed = true;
            }
        }

        if ($changed)
        {
            $params->set('rules', $rules);
        }
    }

    /**
     * Migrates value of CartValue Condition for both Hikashop & VirtueMart.
     * 
     * @param   object  $param
     * 
     * @return  void
     */
    private function updateCartValueCondition(&$params)
    {
        if (!$rules = $params->get('rules', []))
        {
            return;
        }

        /**
         * $rules may be either a string or an array of objects.
         * For this reason, we transform all cases to arrays.
         */
        if (is_array($rules))
        {
            $rules = json_encode($rules);
        }
        
        if (!$rules = json_decode($rules, true))
        {
            return;
        }

        $allowed_rules = [
            'Component\HikashopCartValue',
            'Component\VirtueMartCartValue'
        ];

        $changed = false;

        foreach ($rules as &$ruleset)
        {
            if (!isset($ruleset['rules']))
            {
                continue;
            }
            
            foreach ($ruleset['rules'] as &$rule)
            {
                
                if (!isset($rule['name']))
                {
                    continue;
                }
                
                if (!in_array($rule['name'], $allowed_rules))
                {
                    continue;
                }

                if (!isset($rule['value']))
                {
                    continue;
                }

                if (!is_scalar($rule['value']))
                {
                    continue;
                }

                // Abort if params property is properly set
                if (isset($rule['params']) && is_array($rule['params']) && count($rule['params']) && isset($rule['params']['value']))
                {
                    continue;
                }

                $rule['params'] = [
                    'total' => 'total',
                    'operator' => $rule['operator'],
                    'value2' => '',
                    'exclude_shipping_cost' => isset($rule['params']['exclude_shipping_cost']) ? $rule['params']['exclude_shipping_cost'] : '0'
                ];

                if (isset($rule['operator']))
                {
                    unset($rule['operator']);
                }
                
                $changed = true;
            }
        }

        if ($changed)
        {
            $params->set('rules', $rules);
        }
    }

    /**
     * Removes the templates.json & favorites.json file from EngageBox installations <= 5.2.0.
     * 
     * This is to ensure the customers download the latest templates.json file
     * the first time they upgrade to EngageBox 5.2.0 
     * from the remote endpoint as the existing templates wont work with the
     * new Templates Library.
     * 
     * Also favorites.json contains outdated IDs so we remove it to start clean.
     * 
     * @since  5.2.0
     */
    private function removeTemplatesData()
    {
        if (version_compare($this->installedVersion, '5.2.0', '>')) 
        {
            return;
        }

        $this->deleteTemplatesJSONFile();
        $this->deleteTemplatesFavoritesJSONFile();
    }

    /**
     * Removes the templates/templates.json file.
     * 
     * @return  void
     */
    private function deleteTemplatesJSONFile()
    {
        $path = JPATH_ROOT . '/media/com_rstbox/templates/templates.json';
        if (!file_exists($path))
        {
            return;
        }

        unlink($path);
    }

    /**
     * Removes the templates/favorites.json file.
     * 
     * @return  void
     */
    private function deleteTemplatesFavoritesJSONFile()
    {
        $path = JPATH_ROOT . '/media/com_rstbox/templates/favorites.json';
        if (!file_exists($path))
        {
            return;
        }

        unlink($path);
    }
}