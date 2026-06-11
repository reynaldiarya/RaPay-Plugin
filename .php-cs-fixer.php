<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->exclude(['vendor', 'node_modules'])
    ->name('*.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

$config = new PhpCsFixer\Config();

return $config
    ->setRiskyAllowed(true) // Required for some rules like 'array_syntax' short
    ->setRules([
        // Use PSR-12 standard as the baseline
        '@PSR12' => true,
        
        // --- ARRAY & LIST ---
        'array_syntax' => ['syntax' => 'short'], // Force the use of [] instead of array()
        'trim_array_spaces' => true,
        'no_whitespace_before_comma_in_array' => true,
        'whitespace_after_comma_in_array' => true,
        'trailing_comma_in_multiline' => ['elements' => ['arrays']], // Trailing comma at the end of multiline arrays

        // --- SPACING & WHITESPACE CLEANUP ---
        'no_trailing_whitespace' => true,
        'no_whitespace_in_blank_line' => true,
        'no_extra_blank_lines' => [
            'tokens' => [
                'extra',
                'curly_brace_block',
                'parenthesis_brace_block',
                'square_brace_block',
                'use',
                'throw',
                'return',
                'continue',
                'break',
            ]
        ],
        'blank_line_before_statement' => [
            'statements' => ['return', 'throw', 'try', 'while', 'for', 'foreach', 'do', 'if', 'switch']
        ],
        'single_blank_line_at_eof' => true,

        // --- IMPORTS (USE STATEMENTS) ---
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'no_unused_imports' => true,
        'single_line_after_imports' => true,
        'fully_qualified_strict_types' => true,

        // --- OPERATORS & CONDITIONALS ---
        'concat_space' => ['spacing' => 'one'], // Force a single space for string concatenation (e.g., 'a' . 'b')
        'binary_operator_spaces' => [
            'default' => 'single_space',
            'operators' => [
                '=>' => 'align_single_space_minimal', // Align key => value indentation in arrays
            ]
        ],
        'unary_operator_spaces' => true,
        'object_operator_without_whitespace' => true,
        'ternary_operator_spaces' => true,
        'standardize_not_equals' => true, // Convert <> to !=
        'yoda_style' => [
            'equal' => false,
            'identical' => false,
            'less_and_greater' => false
        ], // Disable Yoda style (changes `if (true === $a)` to `if ($a === true)`)

        // --- PHPDOC ---
        'phpdoc_align' => ['align' => 'vertical'], // Align @param, @var, etc.
        'phpdoc_indent' => true,
        'phpdoc_no_empty_return' => true,
        'phpdoc_order' => true,
        'phpdoc_scalar' => true,
        'phpdoc_separation' => true,
        'phpdoc_single_line_var_spacing' => true,
        'phpdoc_trim' => true,
        'phpdoc_types' => true,
        'no_empty_phpdoc' => true,

        // --- FUNCTIONS & CLASSES ---
        'function_typehint_space' => true,
        'visibility_required' => [
            'elements' => ['property', 'method', 'const']
        ],
        'single_trait_insert_per_statement' => true,
        
        // --- PHP TAGS ---
        'full_opening_tag' => true,
        'no_closing_tag' => true, // Remove closing tag at the end of pure PHP files (prevents whitespace leakage)
    ])
    ->setFinder($finder);