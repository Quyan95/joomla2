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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Http\HttpFactory;
use Joomla\Registry\Registry;

class plgConvertFormsAppsWebHooks extends App
{
	/**
	 * The trigger that sends the email
	 *
	 * @return void
	 */
	public function actionWebHook()
	{
        $options = new Registry();
        $options->set('headers.Content-Type', $this->options['format'] == 'json' ? 'application/json' : 'application/x-www-form-urlencoded');
        $options->set('headers.sendby', 'Convert Forms');
        $options->set('timeout', 120);

        // Prepare URL query string parameters
        $data = new Registry();

        if ($this->options['data'])
        {
            foreach ($this->options['data'] as $item)
            {
                if (!$item)
                {
                    continue;
                }

                $data->set($item['key'], $item['value'], $this->options['unflatten'] ? '__' : null);
            }

            $data = json_encode($data->toArray());
        }

        // Prepare request headers
        if ($headers = array_filter($this->options['headers']))
        {
            foreach ($headers as $header)
            {
                $options->set('headers.' . $header['key'], $header['value']);
            }
        }

        // Prepare the HTTP request
        $http = HttpFactory::getHttp($options);

        // Call the Webhook depending on the request method
        $url = $this->options['url'];
        $method = $this->options['method'];

        try
		{
            switch ($method)
            {
                case 'options':
                case 'head':
                case 'get':
                case 'delete':
                case 'trace':
                    $response = $http->{$method}($url);
                    break;
                
                default:
                    $response = $http->{$method}($url, $data);
            }

		} catch (\RuntimeException $e)
		{
			$this->setError('Unable to open webhook.' . $e->getCode() . '-' . $e->getMessage());
			return false;
		}

        $body = json_decode((string) $response->body, true);

        if ($response === null || $response->code > 299)
        {
            // Try to detect more details about the error message
            $error = [
                'code'   => $response->getStatusCode() . ' ' . $response->getReasonPhrase(),
                'title'  => null,
                'detail' => null,
            ];

            if ($body)
            {   
                if (isset($body['errors']))
                {
                    $thisError = $body['errors'];

                    if (isset($thisError[0]))
                    {
                        $thisError = $thisError[0];
                    }

                    if (isset($thisError['title']))
                    {
                        $error['title'] = $thisError['title'];
                    }

                    if (isset($thisError['detail']))
                    {
                        $error['detail'] = $thisError['detail'];
                    }
                }

                if (isset($body['title']))
                {
                    $error['title'] = $body['title'];
                }

                if (isset($body['detail']))
                {
                    $error['detail'] = $body['detail'];
                }
            }

			$this->setError(implode('. ' , array_filter($error)));
        }

        return $body;
	}

    /**
     * Get a list with the fields needed to setup the app's event.
     *
     * @return array
     */
	public function getActionWebHookSetupFields()
	{
        return [
            [
                'name' => Text::_('COM_CONVERTFORMS_APP_SETUP_ACTION'),
                'fields' => [
                    $this->field('url', [
                        'includeSmartTags' => 'Fields'
                    ]),
                    $this->field('method', [
                        'includeSmartTags' => false,
                        'value' => 'get',
                        'options' => [
                            [
                                'label' => 'GET',
                                'value' => 'get'
                            ],
                            [
                                'label' => 'POST',
                                'value' => 'post',
                            ],
                            [
                                'label' => 'PUT',
                                'value' => 'put'
                            ],
                            [
                                'label' => 'PATCH',
                                'value' => 'patch'
                            ],
                            [
                                'label' => 'HEAD',
                                'value' => 'head'
                            ],
                            [
                                'label' => 'DELETE',
                                'value' => 'delete'
                            ],
                            [
                                'label' => 'OPTIONS',
                                'value' => 'options'
                            ],
                            [
                                'label' => 'TRACE',
                                'value' => 'trace'
                            ],
                        ]
                    ]),
                    $this->field('headers', [
                        'type' => 'keyvalue',
                        'required' => false
                    ]),
                    $this->field('data', [
                        'type' => 'keyvalue',
                        'required' => false
                    ]),
                    $this->field('unflatten', [
                        'type' => 'bool',
                        'value' => 1,
                        'includeSmartTags' => false,
                        'required' => false
                    ]),
                    $this->field('format', [
                        'value' => 'json',
                        'options' => [
                            [
                                'label' => 'JSON',
                                'value' => 'json',
                            ],
                            [
                                'label' => 'FORM',
                                'value' => 'form'
                            ],
                        ],
                        'includeSmartTags' => false,
                        'required' => false
                    ]),
                ]
            ]
        ];
	}
}