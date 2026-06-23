<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->in([
        __DIR__ . '/bank',
        __DIR__ . '/blocks',
        __DIR__ . '/e-money',
    ])
    ->append([
        __DIR__ . '/indobe-for-woocommerce.php',
    ])
    ->exclude([
        'vendor',
        '.git',
        '.idea',
    ])
    ->name('*.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

return (new Config())
    ->setRiskyAllowed(true)
    ->setFinder($finder)
    ->setRules([
        '@PSR12' => true,
        '@PHP83Migration' => true,

        'declare_strict_types' => true,

        'array_indentation' => true,
        'trim_array_spaces' => true,
        'trailing_comma_in_multiline' => [
            'elements' => [
                'arrays',
                'arguments',
                'parameters',
                'match',
            ],
        ],

        'ordered_imports' => [
            'sort_algorithm' => 'alpha',
        ],
        'single_import_per_statement' => true,
        'no_unused_imports' => true,

        'no_trailing_whitespace' => true,
        'no_whitespace_in_blank_line' => true,
        'single_blank_line_at_eof' => true,
        'blank_line_after_opening_tag' => true,
        'blank_line_after_namespace' => true,

        'no_extra_blank_lines' => [
            'tokens' => [
                'break',
                'continue',
                'return',
                'throw',
                'use',
            ],
        ],

        'class_attributes_separation' => [
            'elements' => [
                'method' => 'one',
                'property' => 'one',
                'trait_import' => 'none',
            ],
        ],

        'function_declaration' => true,
        'single_line_empty_body' => true,

        'binary_operator_spaces' => [
            'default' => 'single_space',
        ],

        'single_quote' => true,

        'phpdoc_align' => true,
        'phpdoc_trim' => true,
        'phpdoc_scalar' => true,
        'phpdoc_separation' => true,
        'phpdoc_summary' => false,
        'phpdoc_types_order' => [
            'null_adjustment' => 'always_last',
        ],

        'no_closing_tag' => false,
    ]);