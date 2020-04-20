<?php

namespace App\View\Composers;

use Roots\Acorn\View\Composer;

class LanguageMenu extends Composer
{
    /**
     * List of views served by this composer.
     *
     * @var array
     */
    protected static $views = [
        'partials.language-menu',
    ];

    /**
     * Data to be passed to view before rendering.
     *
     * @return array
     */
    public function with(): array
    {
        return [
            'language_menu' => $this->languageMenu(),
        ];
    }

    /**
     * Returns the primary navigation.
     *
     * @return array
     */
    public function languageMenu(): array
    {
        $languages = apply_filters('wpml_active_languages', null, [
            'skip_missing' => 0,
            'orderby' => 'code',
            'order' => 'desc',
        ]);

        return collect($languages)
            ->map(function ($language) {
                $item = new \stdClass;
                $item->active = $language['active'];
                $item->activeAncestor = null;
                $item->title = $language['native_name'];
                $item->url = $language['url'];
                $item->label = strtoupper($language['language_code']);
                $item->disabled = $language['missing'];
                $item->children = false;
                return $item;
            })
            ->toArray();
    }
}
