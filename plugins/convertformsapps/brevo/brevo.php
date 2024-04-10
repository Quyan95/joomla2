<?php

/**
 * @package         Convert Forms
 * @version         4.2.4 Pro
 * 
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2023 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

defined('_JEXEC') or die('Restricted access');

use ConvertForms\Tasks\App;
use ConvertForms\Tasks\Helper;
use ConvertForms\Tasks\AppConnection;
use Joomla\CMS\Language\Text;
use NRFramework\Integrations\Brevo;

class plgConvertFormsAppsBrevo extends App
{   
    // This app requires connection with a 3rd party API
    use AppConnection;

	/**
	 * The Subscribe trigger
	 *
	 * @return void
	 */
	public function actionSubscribe()
	{
        $api = $this->getApiOrDie();

        // Calculate merge tags
        $keysToRemove = [
            'list',
            'email',
            'update_existing_subscriber'
        ];

        $merge_tags = array_diff_key($this->options, array_flip($keysToRemove));

        // Set properties in proper format
        $properties = isset($merge_tags['properties']) ? $merge_tags['properties'] : [];

        if ($properties)
        {
            // Get new properties format
            $new_properties = [];
            foreach ($properties as $key => $value)
            {
                if (!isset($value['value']))
                {
                    continue;
                }
                
                $new_properties[$value['key']] = $value['value'];
            }

            // Delete old properties value
            unset($merge_tags['properties']);
            
            // Add new properties to the merge tags
            $merge_tags = array_merge($merge_tags, $new_properties);
        }

        $api->subscribe(
            $this->options['email'],
            $merge_tags,
            $this->options['list'],
            $this->options['update_existing_subscriber'] === '1'
        );

		if (!$api->success())
        {
            $this->setError($api->getLastError());
        }
	}

    /**
     * Get a list with the fields needed to setup the app's event.
     *
     * @return array
     */
	public function getActionSubscribeSetupFields()
	{
        $matchFields = [
            $this->commonField('email'),
            $this->field('properties', [
                'type' => 'keyvalue',
                'required' => false,
                'keyField' => [
                    'type' => 'select',
                    'options' => $this->getAttributesList(),
                    'placeholder' => $this->lang('SELECT_PROPERTY')
                ]
            ]),
        ];

        $fields = [
            [
                'name' => Text::_('COM_CONVERTFORMS_APP_SETUP_ACTION'),
                'fields' => [
                    $this->field('list', [
                        'required' => false,
                        'loadOptions' => $this->getAjaxEndpoint('getLists'),
                        'includeSmartTags' => 'Fields'
                    ]),
                    $this->commonField('update_existing_subscriber')
                ]
            ],
            [
                'name' => Text::_('COM_CONVERTFORMS_APP_MATCH_FIELDS'),
                'fields' => $matchFields
            ]
        ];

        return $fields;
	}
    
    /**
     * Get all lists.
     * 
     * @return  array
     */
    public function getLists()
    {
        $api = $this->getApiOrDie();
        
        if (!$lists = $api->getLists())
        {
            return [];
        }

        return array_map(function($list)
        {
            return [
                'value' => $list['id'],
                'label' => $list['name'],
                'desc'  => $list['id'],
            ];
        }, $lists);
    }

    /**
     * Test an app's connection with the given credentials
     *
     * @return void
     */
	public function testConnection($connection_options = null)
    {
        if ($connection_options)
        {
            $this->setConnection($connection_options);
        }

        $api = $this->getApiOrDie();

        $api->get('account');

        if (!$api->success())
		{
            $this->setError($api->getLastError());
            return false;
        }

        return true;
	}

    /**
     * Return all account's attributes required by the Attributes repeater field.
     *
     * @return array
     */
    private function getAttributesList()
    {
        $api = $this->getApiOrDie();
        $attributes = $api->get('contacts/attributes');

        $props = [];

        if (!isset($attributes['attributes']))
        {
            return $props;
        }

        foreach ($attributes['attributes'] as $property)
        {
            $props[] = [
                'value' => $property['name'],
                'label' => $property['name'],
                'desc'  => ''
            ];
        }

        return $props;
    }
    
    /**
     * Return the app's respective class 
     *
     * @return object
     */
    private function getApiOrDie()
    {
        if (!isset($this->connection) || !$this->connection)
        {
            throw new \Exception('Invalid connection');
        }

        if (!$api_key = $this->connection->get('api_key'))
        {
            throw new \Exception('Invalid API Key supplied.');
        }

        return new Brevo($api_key);
    }
}