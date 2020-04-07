<?php

namespace Genero\Sage\Polylang;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class TranslationFinder
{
    /** @var StringFinder */
    protected $stringFinder;

    /** @var array */
    protected $domains = [];

    public function domains()
    {
        if (!$this->domains) {
            $this->domains = get_option('sage_pll_domains') ?: [];
        }
        return $this->domains;
    }

    public function shouldScan()
    {
        return $this->isPolylangStringTablePage();
    }

    /**
     * Add Polylang fallback translation for when a registred domain is used.
     *
     * @param  string $translation
     * @param  string $text
     * @param  string $domain
     * @return string
     */
    public function gettext(string $translation, string $text, string $domain): string
    {
        // It's not a tracked domain, exit.
        if (!in_array($domain, $this->domains())) {
            return $translation;
        }
        // Translate using polylang.
        $pll_translation = pll__($text);

        // If there's a Polylang translation it takes precedence over WP core.
        return $pll_translation !== $text ? $pll_translation : $translation;
    }

    /**
     * Register translations with Polylang and setup the tracked domains.
     */
    public function registerTranslations(array $strings): void
    {
        $this->registerStrings($strings);

        $domains = collect($strings)
            ->pluck('domain')
            ->unique()
            ->values()
            ->all();

        $this->registerDomains($domains);
    }

    /**
     * Register strings with Polylang.
     */
    protected function registerStrings(array $strings): void
    {
        foreach ($strings as $id => $string) {
            pll_register_string($string['search'], $string['search'], $string['domain']);
        }
    }

    /**
     * Register tracked domains.
     */
    protected function registerDomains(array $domains): void
    {
        update_option('sage_pll_domains', $domains, true);
    }

    /**
     * Return if this is a Polylang string table page.
     */
    protected function isPolylangStringTablePage(): bool
    {
        global $pagenow;
        if (!is_admin() || !isset($_GET['page']) || empty($pagenow)) {
            return false;
        }
        if ($pagenow === 'options-general.php') {
            // wp-admin/options-general.php?page=mlang&tab=strings
            return $_GET['page'] === 'mlang' && isset($_GET['tab']) && $_GET['tab'] === 'strings';
        } elseif ($pagenow === 'admin.php') {
            // wp-admin/admin.php?page=mlang_strings
            return $_GET['page'] === 'mlang_strings';
        }
        return false;
    }
}
