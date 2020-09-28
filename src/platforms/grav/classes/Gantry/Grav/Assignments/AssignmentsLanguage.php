<?php

/**
 * @package   Gantry5
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2020 RocketTheme, LLC
 * @license   MIT
 *
 * http://opensource.org/licenses/MIT
 */

namespace Gantry\Grav\Assignments;

use Gantry\Component\Assignments\AssignmentsInterface;
use Grav\Common\Grav;
use Grav\Common\Language\Language;
use Grav\Common\Page\Interfaces\PageInterface;

/**
 * Class AssignmentsLanguage
 * @package Gantry\Grav\Assignments
 */
class AssignmentsLanguage implements AssignmentsInterface
{
    /** @var string */
    public $type = 'language';
    /** @var int */
    public $priority = 1;

    /**
     * Returns list of rules which apply to the current page.
     *
     * @return array
     */
    public function getRules()
    {
        $grav = Grav::instance();

        /** @var Language $language */
        $language = $grav['language'];
        if (!$language->enabled()) {
            return [];
        }

        $tag = $language->getActive() ?: $language->getDefault();
        $rules[$tag] = $this->priority;

        return [$rules];
    }

    /**
     * List all the rules available.
     *
     * @param string $configuration
     * @return array
     */
    public function listRules($configuration)
    {
        $grav = Grav::instance();

        /** @var Language $language */
        $language = $grav['language'];
        if (!$language->enabled()) {
            return [];
        }

        // Get label and items for each menu
        $list = [
                'label' => 'Languages',
                'items' => $this->getItems($language)
        ];

        return [$list];
    }

    /**
     * @param Language $language
     * @return array
     */
    protected function getItems(Language $language)
    {
        $languages = $language->getLanguages();

        $items = [];

        /** @var PageInterface $page */
        foreach ($languages as $code) {
            $items[] = [
                'name' => $code,
                'label' => ucfirst($code),
            ];
        }

        return $items;
    }
}
