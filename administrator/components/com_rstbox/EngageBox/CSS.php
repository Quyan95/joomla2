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

defined('_JEXEC') or die('Restricted access');

class CSS
{
	/**
	 * The box settings.
	 * 
	 * @var  object
	 */
	protected $box;
	
	public function __construct($box = [])
	{
		$this->box = $box;
	}

	public function generate()
	{
        $instance_css_selector = '.eb-' . $this->box->id . '.eb-inst';
        $dialog_css_selector = '.eb-' . $this->box->id . ' .eb-dialog';
        $overlay_css_selector = '.eb-' . $this->box->id . ' .eb-backdrop';
        $container_css_selector = '.eb-' . $this->box->id . ' .eb-dialog .eb-container';
        $closebutton_css_selector = '.eb-' . $this->box->id . ' .eb-close';

        $position = is_string($this->box->position) && json_decode($this->box->position, true) ? json_decode($this->box->position, true) : $this->box->position;

		$paramsArray = $this->box->params->toArray();
		
		$controls = [
            [
                'selector' => $dialog_css_selector,
                'property' => '--eb-max-width',
                'value' => isset($paramsArray['width']) ? $paramsArray['width'] : null
            ],
            [
                'selector' => $dialog_css_selector,
                'property' => '--eb-height',
                'value' => isset($paramsArray['height']) ? $paramsArray['height'] : null
            ],
            [
                'selector' => $dialog_css_selector,
                'property' => '--eb-padding',
                'type' => 'Spacing',
                'value' => isset($paramsArray['padding']) ? $paramsArray['padding'] : null
            ],
            [
                'selector' => $instance_css_selector,
                'property' => '--eb-margin',
                'type' => 'Spacing',
                'value' => isset($paramsArray['margin']) ? $paramsArray['margin'] : null
            ],
            [
                'selector' => $dialog_css_selector,
                'property' => '--eb-border-radius',
                'type' => 'Spacing',
                'value' => isset($paramsArray['borderradius']) ? $paramsArray['borderradius'] : null
            ],
            [
                'selector' => $dialog_css_selector,
                'property' => '--eb-background-color',
                'value' => isset($paramsArray['backgroundcolor']) ? $paramsArray['backgroundcolor'] : null
            ],
            [
                'selector' => $dialog_css_selector,
                'property' => '--eb-text-color',
                'value' => isset($paramsArray['textcolor']) ? $paramsArray['textcolor'] : null
            ],
            // Close button
            [
                'selector' => $closebutton_css_selector,
                'value' => isset($paramsArray['closebutton']['show']) ? $paramsArray['closebutton']['show'] : null,
				'values' => [
					// Hide
					'0' => [
						'--eb-close-button-inside:none;',
						'--eb-close-button-outside:none;'
					]
				]
            ],
            [
                'selector' => $closebutton_css_selector,
                'value' => isset($paramsArray['closebutton']['show']) ? $paramsArray['closebutton']['show'] : null,
				'values' => [
					// Show Inside
					'1' => [
						'--eb-close-button-inside:block;',
						'--eb-close-button-outside:none;'
					]
				]
            ],
            [
                'selector' => $closebutton_css_selector,
                'value' => isset($paramsArray['closebutton']['show']) ? $paramsArray['closebutton']['show'] : null,
				'values' => [
					// Show Outside
					'2' => [
						'--eb-close-button-outside:block;',
						'--eb-close-button-inside:none;'
					]
				]
            ],
            [
                'selector' => $closebutton_css_selector,
                'value' => isset($paramsArray['closebutton']['source']) ? $paramsArray['closebutton']['source'] : null,
				'values' => [
					// Icon
					'icon' => [
						'--eb-close-button-icon:block;',
						'--eb-close-button-image:none;'
					]
				]
            ],
            [
                'selector' => $closebutton_css_selector,
                'value' => isset($paramsArray['closebutton']['source']) ? $paramsArray['closebutton']['source'] : null,
				'values' => [
					// Image
					'image' => [
						'--eb-close-button-image:block;',
						'--eb-close-button-icon:none;'
					]
				]
            ],
            [
                'selector' => $closebutton_css_selector,
                'property' => '--eb-close-button-font-size',
                'value' => isset($paramsArray['closebutton']['size']) ? $paramsArray['closebutton']['size'] : null,
                'unit' => isset($paramsArray['closebutton']['size']['unit']) ? $paramsArray['closebutton']['size']['unit'] : 'px'
            ],
            [
                'selector' => $closebutton_css_selector,
                'property' => [
                    '--eb-close-button-visibility' => 'hidden',
                    '--eb-close-button-animation' => '%value% ebFadeIn',
                    '--eb-close-button-animation-fill-mode' => 'forwards',
                ],
                'value' => isset($paramsArray['closebutton']['delay']) ? $paramsArray['closebutton']['delay'] : null,
                'unit' => isset($paramsArray['closebutton']['delay']['unit']) ? $paramsArray['closebutton']['delay']['unit'] : 'ms',
            ],
            [
                'selector' => $closebutton_css_selector,
                'property' => '--eb-close-button-color',
                'value' => isset($paramsArray['closebutton']['color']) ? $paramsArray['closebutton']['color'] : null
            ],
            [
                'selector' => $closebutton_css_selector,
                'property' => '--eb-close-button-hover-color',
                'value' => isset($paramsArray['closebutton']['hover']) ? $paramsArray['closebutton']['hover'] : null
            ],
            [
                'selector' => $closebutton_css_selector . ' > img',
                'property' => [
					'content' => 'url("' . \JURI::root() . '%value%")',
				],
                'value' => isset($paramsArray['closebutton']['image']) ? $paramsArray['closebutton']['image'] : null
            ],
            [
                'selector' => $dialog_css_selector,
                'property' => [
					'--eb-dialog-shadow' => 'var(--eb-shadow-%value%)',
				],
                'value' => isset($paramsArray['boxshadow']) ? $paramsArray['boxshadow'] : null,
            ],
            [
                'selector' => $dialog_css_selector,
                'value' => isset($paramsArray['aligncontent']) ? $paramsArray['aligncontent'] : null,
				'values' => [
					// Horizontal Left
					'acl' => [
						'text-align:left;'
					],
					// Horizontal Center
					'acc' => [
						'text-align:center;'
					],
					// Horizontal Right
					'acr' => [
						'text-align:right;'
					]
				]
            ],
            [
                'selector' => $container_css_selector,
                'value' => isset($paramsArray['aligncontent']) ? $paramsArray['aligncontent'] : null,
				'values' => [
					// Vertical Top
					'act' => [
						'justify-content:flex-start;',
						'min-height:100%;',
						'display:flex;',
						'flex-direction:column;'
					],
					// Vertical Middle
					'acm' => [
						'justify-content:center;',
						'min-height:100%;',
						'display:flex;',
						'flex-direction:column;'
					],
					// Vertical Bottom
					'acb' => [
						'justify-content:flex-end;',
						'min-height:100%;',
						'display:flex;',
						'flex-direction:column;'
					]
				]
            ],
			[
				'selector' => $dialog_css_selector,
				'property' => '--eb-border-style',
				'value' => isset($paramsArray['bordertype']) ? $paramsArray['bordertype'] : null
			],
			[
				'selector' => $dialog_css_selector,
				'property' => '--eb-border-color',
				'value' => isset($paramsArray['bordercolor']) ? $paramsArray['bordercolor'] : null
			],
			[
				'selector' => $dialog_css_selector,
				'property' => '--eb-border-width',
				'value' => isset($paramsArray['borderwidth']) ? $paramsArray['borderwidth'] : null
			],
            // 
            [
                'selector' => $dialog_css_selector,
                'property' => '--eb-overlay-enabled',
                'value' => isset($paramsArray['overlay']) ? $paramsArray['overlay'] : null,
                'skip_inherit_value' => true
            ],
            [
                'selector' => $overlay_css_selector,
                'property' => '--eb-overlay-background-color',
                'value' => isset($paramsArray['overlay_color']) ? $paramsArray['overlay_color'] : null,
                'conditions' => [
                    [
                        'property' => '--eb-overlay-enabled',
                        'value' => '1'
                    ]
                ],
            ],
            [
                'selector' => $dialog_css_selector,
                'property' => '--eb-overlay-clickable',
                'value' => isset($paramsArray['overlayclick']) ? $paramsArray['overlayclick'] : null,
                'conditions' => [
                    [
                        'property' => '--eb-overlay-enabled',
                        'value' => '1'
                    ]
                ],
            ],
            [
                'selector' => $overlay_css_selector,
                'property' => [
                    '--eb-overlay-blur' => '%value_raw%'
                ],
                'conditions' => [
                    [
                        'property' => '--eb-overlay-enabled',
                        'value' => '1'
                    ]
                ],
                'value' => isset($paramsArray['blur_bg']) ? $paramsArray['blur_bg'] : null
            ],
            [
                'selector' => $dialog_css_selector,
                'property' => '--eb-bg-image-enabled',
                'value' => isset($paramsArray['bgimage']) ? $paramsArray['bgimage'] : null,
            ],
            [
                'selector' => $dialog_css_selector,
                'property' => [
                    '--eb-background-image' => 'url(\'' . \JURI::root() . '%value%\')',
                ],
                'fallback_property' => [
                    '--eb-background-image' => 'none',
                ],
                'conditions' => [
                    [
                        'property' => '--eb-bg-image-enabled',
                        'value' => '1'
                    ]
                ],
                'value' => isset($paramsArray['bgimagefile']) ? $paramsArray['bgimagefile'] : null,
            ],
            [
                'selector' => $dialog_css_selector,
                'property' => '--eb-background-repeat',
                'conditions' => [
                    [
                        'property' => '--eb-bg-image-enabled',
                        'value' => '1',
                    ]
                ],
                'value' => isset($paramsArray['bgrepeat']) ? $paramsArray['bgrepeat'] : null,
            ],
            [
                'selector' => $dialog_css_selector,
                'property' => '--eb-background-size',
                'conditions' => [
                    [
                        'property' => '--eb-bg-image-enabled',
                        'value' => '1',
                    ]
                ],
                'value' => isset($paramsArray['bgsize']) ? $paramsArray['bgsize'] : null,
            ],
            [
                'selector' => $dialog_css_selector,
                'property' => '--eb-background-position',
                'conditions' => [
                    [
                        'property' => '--eb-bg-image-enabled',
                        'value' => '1',
                    ]
                ],
                'value' => isset($paramsArray['bgposition']) ? $paramsArray['bgposition'] : null,
            ],
            
            // Position
            [
                'selector' => $instance_css_selector,
                'value' => $position,
                'values' => [
					// Top Left
					'top-left' => [
						'justify-content:flex-start;',
                        'align-items:flex-start;'
					],
					// Top Center
					'top-center' => [
						'justify-content:center;',
                        'align-items:flex-start;'
					],
					// Top Right
					'top-right' => [
						'justify-content:flex-end;',
                        'align-items:flex-start;'
					],
					// Middle Left
					'middle-left' => [
                        'justify-content:initial;',
						'align-items:center;',
                    ],
					// Middle Center
					'center' => [
						'justify-content:center;',
						'align-items:center;'
                    ],
					// Middle Right
					'middle-right' => [
                        'justify-content:flex-end;',
						'align-items:center;'
                    ],
					// Bottom Left
					'bottom-left' => [
                        'justify-content:flex-start;',
						'align-items:flex-end;'
                    ],
					// Bottom Center
					'bottom-center' => [
						'justify-content:center;',
						'align-items:flex-end;'
                    ],
					// Bottom Right
					'bottom-right' => [
						'justify-content:flex-end;',
						'align-items:flex-end;'
                    ],
				]
            ],
		];

		$controlsInstance = new \NRFramework\Controls\Controls();

        if (!$controlsCSS = $controlsInstance->generateCSS($controls))
        {
            return;
        }

        \JFactory::getDocument()->addStyleDeclaration($controlsCSS);
	}
}