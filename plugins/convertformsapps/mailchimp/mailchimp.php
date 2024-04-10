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
use NRFramework\Integrations\MailChimp;
class plgConvertFormsAppsMailChimp extends App
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
            'audience',
            'interests',
            'interests_replace',
            'email',
            'tags',
            'tags_replace',
            'update_existing_subscriber',
            'double_optin',
        ];

        $merge_tags = array_diff_key($this->options, array_flip($keysToRemove));

        $api->subscribeV2(
            $this->options['audience'],
            $this->options['email'],
            $merge_tags,
            $this->options['double_optin'],
            $this->options['update_existing_subscriber'],
            Helper::readRepeatSelect($this->options['tags']),
            $this->options['tags_replace'],
            Helper::readRepeatSelect($this->options['interests']),
            isset($this->options['interests_replace']) ? $this->options['interests_replace'] : 'add_only',
        );

		if (!$api->success())
        {
            $this->setError($api->getLastError());
        }

        $response = $api->getLastResponse()->body;
        $response['isNew'] = $api->getLastRequest()['method'] == 'post';

        return $response;
	}

    /**
     * Get a list with the fields needed to setup the app's event.
     *
     * @return array
     */
	public function getActionSubscribeSetupFields()
	{
        $api = $this->getApiOrDie();
        
        $audience = $this->params->get('options.audience');

        $mergeTags = [
            $this->commonField('email')
        ];

        if ($audience)
        {
            if ($mergeFields = $this->getAudienceMergeFields($audience))
            {
                foreach ($mergeFields as $mergeField)
                {
                    $mergeTags[] = $this->field($mergeField['tag'], [
                        'label' => $mergeField['name'],
                        'hint'  => Text::sprintf('PLG_CONVERTFORMSAPPS_MAILCHIMP_MERGE_TAG', $mergeField['tag']),
                        'required' => $mergeField['required']
                    ]);
                }
            }
        }
        
        $fields = [
            [
                'name' => Text::_('COM_CONVERTFORMS_APP_SETUP_ACTION'),
                'fields' => [
                    $this->field('audience', [
                        'label'   => Text::_('COM_CONVERTFORMS_APP_AUDIENCE'),
                        'hint'    => Text::sprintf('COM_CONVERTFORMS_APP_AUDIENCE_DESC', $this->lang('ALIAS')),
                        'loadOptions' => $this->getAjaxEndpoint('getAudiences'),
                        'refresh' => true,
                        'includeSmartTags' => 'Fields'
                    ]),
                    $this->commonField('update_existing_subscriber'),
                    $this->commonField('double_optin'),
                ]
            ],
            [
                'name' => Text::_('COM_CONVERTFORMS_APP_MATCH_FIELDS'),
                'fields' => $mergeTags
            ]
        ];

        if ($audience && strpos($audience, '{') === false)
        {
            $fields[] = [
                'name'   => 'Interest Groups',
                'fields' => [
                    $this->field('interests', [
                        'type'    => 'repeat-select',
                        'required' => false,
                        'loadOptions' => $this->getAjaxEndpoint('getAudienceGroups') . '&audience=' . $audience,
                        'includeSmartTags' => 'Fields'
                    ]),
                    $this->field('interests_replace', [
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
            ];
        }

        $fields[] = [
            'name'   => Text::_('COM_CONVERTFORMS_APP_TAGS'),
            'fields' => [
                $this->field('tags', [
                    'type'  => 'repeat-select',
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
        ];

        return $fields;
	}

    private function getAudienceMergeFields($audience_id)
    {
        $api = $this->getApiOrDie();
        $result = $api->get('/lists/' . $audience_id . '/merge-fields');

        return isset($result['merge_fields']) ? $result['merge_fields'] : false;
    }

    public function getAudienceGroups()
    {
        $api = $this->getApiOrDie();

        $audience_id = $this->params->get('audience');

        $interestGroups = $api->get('/lists/' . $audience_id . '/interest-categories');

        $listInterests = [];

        foreach ($interestGroups['categories'] as $interestGroup)
        {
            $interests = $api->get('/lists/' . $audience_id . '/interest-categories/' . $interestGroup['id'] . '/interests');

            if (isset($interests['interests']))
            {
                foreach ($interests['interests'] as $interest)
                {
                    $listInterests[] = [
                        'value' => $interest['id'],
                        'label' => $interestGroup['title'] . ' - ' . $interest['name'],
                        'desc' => $interest['id'],
                    ];
                }
            }
        }

        return $listInterests;
    }

    public function getAudiences()
    {
        $api = $this->getApiOrDie();
        
        if (!$lists = $api->getLists())
        {
            throw new Exception('No lists found');
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

        $api->get('/ping');

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

        $integration = new \NRFramework\Integrations\MailChimp($api_key);

        return $integration;
    }
}