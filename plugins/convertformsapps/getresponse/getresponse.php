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

use Joomla\CMS\Language\Text;
use ConvertForms\Tasks\Helper;
use ConvertForms\Tasks\App;
use ConvertForms\Tasks\AppConnection;

class plgConvertFormsAppsGetResponse extends App
{
    // This app requires connection with a 3rd party API
    use AppConnection;

    public function actionSubscribe()
	{
        $api = $this->getApiOrDie();

        $custom_fields = isset($this->options['custom_fields']) ? $this->options['custom_fields'] : [];
        if ($custom_fields)
        {
            // Get new custom fields format
            $new_custom_fields = [];
            foreach ($custom_fields as $key => $value)
            {
                $new_custom_fields[$value['key']] = $value['value'];
            }

            $custom_fields = $new_custom_fields;
        }

        $api->subscribe(
            $this->options['email'],
            $this->options['name'],
            $this->options['list'],
            $custom_fields,
            $this->options['update_existing_subscriber'] === '1',
            $this->options['dayOfCycle'],
            Helper::readRepeatSelect($this->options['tags']),
            $this->options['tags_replace']
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
        $api = $this->getApiOrDie();
        
        $mergeTags = [
            $this->commonField('email'),
            $this->field('name', [
                'required' => false
            ]),
            $this->field('dayOfCycle', [
                'required' => true,
                'value' => '0'
            ]),
        ];

        // Add custom fields
        if ($custom_fields = $this->getCustomFields())
        {
            $mergeTags[] = $this->field('custom_fields', [
                'type' => 'keyvalue',
                'required' => false,
                'keyField' => [
                    'type' => 'select',
                    'options' => $custom_fields,
                    'placeholder' => $this->lang('SELECT_CUSTOM_FIELD')
                ]
            ]);
        }

        $fields = [
            [
                'name' => Text::_('COM_CONVERTFORMS_APP_SETUP_ACTION'),
                'fields' => [
                    $this->field('list', [
                        'loadOptions' => $this->getAjaxEndpoint('getLists'),
                        'includeSmartTags' => 'Fields'
                    ]),
                    $this->commonField('update_existing_subscriber')
                ]
            ],
            [
                'name' => Text::_('COM_CONVERTFORMS_APP_MATCH_FIELDS'),
                'fields' => $mergeTags
            ],
            [
                'name'   => Text::_('COM_CONVERTFORMS_APP_TAGS'),
                'fields' => [
                    $this->field('tags', [
                        'type'  => 'repeat-select',
                        'options' => $this->getTags(),
                        'label' => Text::_('COM_CONVERTFORMS_APP_TAGS'),
                        'hint'  => Text::_('COM_CONVERTFORMS_APP_TAGS_DESC'),
                        'required' => false
                    ]),
                    $this->field('tags_replace', [
                        'required' => false,
                        'value' => 'add_only',
                        'options' => [
                            [
                                'label' => Text::_('COM_CONVERTFORMS_APP_REPLACE_ADD_ONLY'),
                                'value' => 'add_only'
                            ],
                            [
                                'label' => Text::_('COM_CONVERTFORMS_APP_REPLACE_REPLACE_ALL'),
                                'value' => 'replace_all'
                            ],
                        ],
                        'includeSmartTags' => false
                    ])
                ]
            ]
        ];

        return $fields;
	}

    /**
     * Returns all tags.
     * 
     * @return  array
     */
    public function getTags()
    {
        $api = $this->getApiOrDie();
        
        if (!$tags = $api->get('tags'))
        {
            return;
        }

        if (!$api->success())
        {
            return;
        }

        return array_map(function($tag)
        {
            return [
                'value' => $tag['tagId'],
                'label' => $tag['name'],
                'desc'  => $tag['tagId'],
            ];
        }, $tags);
        
    }

    /**
     * Get all custom fields.
     * 
     * @return  array
     */
    private function getCustomFields()
    {
        $api = $this->getApiOrDie();
        
        if (!$custom_fields = $api->get('custom-fields'))
        {
            return;
        }

        if (!$api->success())
        {
            return;
        }

        $fields = [];

        foreach ($custom_fields as $field)
        {
            if ($field['hidden'] === 'true')
            {
                continue;
            }

            $fields[] = [
                'value' => $field['name'],
                'label' => $field['name'],
                'desc'  => $field['fieldType']
            ];
        }
        
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

        $api->get('accounts');

        if (!$api->success())
		{
            $this->setError($api->getLastError());
            return false;
        }

        return true;
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

        $integration = new \NRFramework\Integrations\GetResponse($api_key);

        return $integration;
    }
}