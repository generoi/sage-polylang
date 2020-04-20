<?php

return [
    'translation_finder' => [
        'file_extensions' => [
            'php',
            'inc',
            'twig',
        ],
        'ignore_paths' => [
            'node_modules/',
            'vendor/',
        ],
        'domain_whitelist' => [],
        'paths' => [
            get_template_directory(),
        ],
    ],
];
