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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use NRFramework\Cache;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\String\StringHelper;

class plgConvertFormsAppsContent extends App
{
	/**
	 * Create a new Joomla article
	 *
	 * @return void
	 */
	public function actionCreateArticle()
	{
        $article = array_merge($this->options, [
            'introtext' => '',
            'alias' => $this->generateAlias(),
            'images' => [
                'image_intro' => $this->getImage('image_intro'),
                'image_fulltext' => $this->getImage('image_full')
            ],
            'created_by' => $this->options['created_by'] == 'auto' ? Factory::getApplication()->getIdentity()->id : $this->options['created_by']
        ]);

        // The Tag component expects the ID of existing tags to be strings and the new tags to start with the #new# prefix.
        if (isset($article['tags']) && is_array($article['tags']))
        {
            // If the Tags option is mapped to a checkboxes field, we will end-up with a multi-dimensional array which will won't work. Ensure we have one dimensional array.
            $tags = \Joomla\Utilities\ArrayHelper::flatten($article['tags']);

            $tags = array_map(function($tag)
            {
                // Sanity check.
                if ($tag == '')
                {
                    return;
                }

                if (is_numeric($tag))
                {
                    return (string) $tag;
                }

                // We have a string. Check whether it's aleady a tag and return its ID.
                if ($tagExists = $this->findTag($tag))
                {
                    return (string) $tagExists->value;
                }

                // New tag creation requires the right permissions in the Tag component.
                return '#new#' . $tag;
               
            }, $tags);

            $article['tags'] = array_values(array_unique(array_filter($tags)));
        }

        $model = $this->getArticleModel();

        if (!$model->save($article))
        {
            throw new Exception($model->getError());
        }

        // Make the article availale in the next step.
        return $model->getItem();
    }

    /**
     * Get a list with the fields needed to setup the app's event.
     *
     * @return array
     */
	public function getActionCreateArticleSetupFields()
	{   
        // Get all article categories
        $categories = HTMLHelper::_('category.options', 'com_content');
        array_walk($categories, function($value) { $value->label = $value->text; });

        // Get user access levels
        $accessLevels = HTMLHelper::_('access.assetgroups');
        array_walk($accessLevels, function($value) { $value->label = $value->text; });

        // Get languages
        $languages = HTMLHelper::_('contentlanguage.existing');
        array_walk($languages, function($value) { $value->label = $value->text; });

        array_unshift($languages, [
            'label' => Text::_('JALL'),
            'value' => '*'
        ]);

        return [
            [
                'name' => Text::_('COM_CONVERTFORMS_APP_SETUP_ACTION'),
                'fields' => [
                    $this->field('title'),
                    $this->field('fulltext', ['type' => 'textarea']),
                    $this->field('catid', [
                        'options' => $categories,
                        'includeSmartTags' => 'Fields'
                    ]),
                    $this->field('created_by', [
                        'includeSmartTags' => 'Fields',
                        'options' => [
                            [
                                'label' => $this->lang('DETECT_LOGGED_IN_USER'),
                                'value' => 'auto'
                            ]
                        ]
                    ]),
                    $this->commonField('state'),
                    $this->field('tags', [
                        'options' => $this->getTags(),
                        'includeSmartTags' => 'Fields',
                        'multiple' => true,
                        'required' => false
                    ]),
                    $this->field('access', [
                        'options' => $accessLevels,
                        'value' => '1',
                        'includeSmartTags' => 'Fields'
                    ]),
                    $this->field('publish_up', [
                        'type' => 'date',
                        'required' => false,
                        'includeSmartTags' => 'Fields',
                        'placeholder' => 'Y-M-D H:M:S'
                    ]),
                    $this->field('publish_down', [
                        'type' => 'date',
                        'required' => false,
                        'includeSmartTags' => 'Fields',
                        'placeholder' => 'Y-M-D H:M:S'
                    ]),
                    $this->field('image_intro', ['required' => false]),
                    $this->field('image_full', ['required' => false]),
                    $this->field('featured', [
                        'type'  => 'bool',
                        'value' => '0',
                        'includeSmartTags' => 'Fields',
                        'required' => false
                    ]),
                    $this->field('language', [
                        'options' => $languages,
                        'value' => '*',
                        'includeSmartTags' => 'Fields',
                        'required' => false
                    ]),
                    $this->field('note', ['required' => false]),
                ]
            ]
        ];
	}

    /**
     * Detect an image properly
     *
     * @param string $prop  The property to check for a valid image
     * 
     * @return mixed Null when no image is found, string otherwise.
     */
    private function getImage($prop)
    {
        if (!isset($this->options[$prop]))
        {
            return;
        }

        $prop = $this->options[$prop];

        return is_array($prop) ? array_shift($prop) : $prop;
    }

    /**
     * Check if a tag exists and return its object
     *
     * @param  string $tag  The tag to check if exists
     * 
     * @return array
     */
    private function findTag($tag)
    {
        $results = array_filter($this->getTags(), function($item) use ($tag)
        {
            return StringHelper::strtolower($item->text) == StringHelper::strtolower($tag);
        });

        return array_shift($results);
    }

    /**
     * Get list of all tags
     *
     * @return array
     */
    private function getTags()
    {
        $hash = 'contentTags';

        if (Cache::has($hash))
        {
            return Cache::get($hash);
        }

        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select(
                [
                    $db->quoteName('a.id', 'value'),
                    $db->quoteName('a.path'),
                    $db->quoteName('a.title', 'text'),
                    $db->quoteName('a.level'),
                    $db->quoteName('a.published'),
                    $db->quoteName('a.lft'),
                ]
            )
            ->from($db->quoteName('#__tags', 'a'))
            ->where($db->quoteName('a.lft') . ' > 0')
			->where($db->quoteName('a.published') . '= 1')
            ->order($db->quoteName('a.lft') . ' ASC');
        
        $db->setQuery($query);

        // Add "-" before nested tags, depending on level
        if ($options = $db->loadObjectList())
        {
            foreach ($options as &$option)
            {
                $repeat = (isset($option->level) && $option->level - 1 >= 0) ? $option->level - 1 : 0;
                $option->label = str_repeat('- ', $repeat) . $option->text;
            }
        }

        return Cache::set($hash, $options);
    }

    private function generateAlias()
    {
        if (Factory::getApplication()->get('unicodeslugs') == 1)
        {
            $alias = OutputFilter::stringUrlUnicodeSlug($this->options['title']);
        } else
        {
            $alias = OutputFilter::stringURLSafe($this->options['title']);
        }

        $table = $this->getArticleModel()->getTable();

        while ($table->load(['alias' => $alias, 'catid' => $this->options['catid']]))
        {
            $alias = StringHelper::increment($alias, 'dash');
        }

        return $alias;
    }

    private function getArticleModel()
    {
        // Make sure error messages thrown by the Content component are translated.
        Factory::getLanguage()->load('com_content', JPATH_ADMINISTRATOR);
        Factory::getLanguage()->load('com_content', JPATH_SITE);

        if (defined('nrJ4'))
        {
            $mvcFactory = Factory::getApplication()->bootComponent('com_content')->getMVCFactory();
            return $mvcFactory->createModel('Article', 'Administrator', ['ignore_request' => true]);
        }

        // Joomla 3
        \JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_content/models');
        \JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_content/tables');

        return \JModelLegacy::getInstance('Article', 'ContentModel', ['ignore_request' => true]);
    }
}