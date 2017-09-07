<?php

namespace Genero\Sage;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class Polylang
{
    /** @var string */
    const REGEX_ARG = '[\s]*[\'\"](.*?)[\'\"][\s]*';

    /** @var array */
    protected $files_extensions = [
        'php',
        'inc',
        'twig',
    ];

    /** @var array */
    protected $domains = [];

    public function __construct($dir)
    {
        if ($this->isPolylangPage()) {
            $this->scan($dir);
        }

        if ($domains = get_option('sage_pll_domains')) {
            $this->domains = $domains;
            add_filter('gettext', [$this, 'gettext'], 10, 3);
        }
    }

    /**
     * Add Polylang fallback translation for when a registred domain is used.
     *
     * @param  string $translation
     * @param  string $text
     * @param  string $domain
     * @return string
     */
    public function gettext($translation, $text, $domain)
    {
        // It's not a tracked domain, exit.
        if (!in_array($domain, $this->domains)) {
            return $translation;
        }
        // Translate using polylang.
        $pll_translation = pll__($text);

        // If there's a Polylang translation it takes precedence over WP core.
        return $pll_translation !== $text ? $pll_translation : $translation;
    }

    /**
     * Find and register all strings in the theme.
     *
     * @param string $dir
     */
    public function scan($dir)
    {
        $files = $this->getFilesFromDir($dir);
        $strings = $this->getStrings($files);
        $this->registerStrings($strings);
        $domains = array_keys($strings);
        $this->registerDomains($domains);
    }

    /**
     * Get all matching files from directory.
     *
     * @param  string $dir
     * @return string[]
     */
    public function getFilesFromDir($dir)
    {
        $di = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
        $iterator = new RecursiveIteratorIterator($di);

        $results = [];
        foreach ($iterator as $file) {
            if (!in_array(pathinfo($file, PATHINFO_EXTENSION), $this->files_extensions)) {
                continue;
            }
            $result[] = $file;
        }
        return $result;
    }

    /**
     * Get all translatable strings from the list of files.
     *
     * @param  string[] $files
     * @return array
     */
    public function getStrings($files)
    {
        $strings = [];
        foreach ($files as $file) {
            $regex = '/__\(' . self::REGEX_ARG . '(?:,' . self::REGEX_ARG . ')?\)/s';
            preg_match_all($regex, file_get_contents($file), $matches);

            if (!empty($matches[1])) {
                foreach ($matches[1] as $idx => $string) {
                    $domain = !empty($matches[2][$idx]) ? $matches[2][$idx] : 'default';
                    if (!isset($strings[$domain]) || !in_array($string, $strings[$domain])) {
                        $strings[$domain][] = $string;
                    }
                }
            }
        }
        return $strings;
    }

    /**
     * Register strings with Polylang.
     *
     * @param array $domains Associative array with domains and their strings.
     */
    public function registerStrings($domains)
    {
        if (!empty($domains)) {
            foreach ($domains as $domain => $strings) {
                foreach ($strings as $string) {
                    pll_register_string($string, $string, $domain);
                }
            }
        }
    }

    /**
     * Register tracked domains.
     *
     * @param  array $domains
     * @return bool
     */
    public function registerDomains($domains)
    {
        return update_option('sage_pll_domains', $domains, true);
    }

    /**
     * Return if this is a polylang string table page.
     *
     * @return bool
     */
    protected function isPolylangPage()
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
