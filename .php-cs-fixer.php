<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__) // Scan direktori saat ini
    ->exclude('vendor') // Kecualikan direktori 'lib'
    ->name('*.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

$config = new PhpCsFixer\Config();
return $config->setRules([
    // Menggunakan standar PSR-12 sebagai dasar
    '@PSR12' => true,

    // --- PEMBERSIHAN SPASI & ENTER ---
    
    // 1. Hapus spasi di ujung kanan baris (trailing spaces)
    'no_trailing_whitespace' => true,

    // 2. Hapus baris yang isinya cuma spasi (agar jadi baris kosong murni atau terhapus)
    'no_whitespace_in_blank_line' => true,

    // 3. Hapus enter/baris kosong berlebih (Agresif)
    'no_extra_blank_lines' => [
        'tokens' => [
            'extra',                    // Hapus baris kosong ekstra di mana saja
            'curly_brace_block',        // Hapus baris kosong di awal/akhir kurung {}
            'parenthesis_brace_block',  // Hapus baris kosong di dalam kurung ()
            'square_brace_block',       // Hapus baris kosong di dalam kurung []
            'use',                      // Hapus baris kosong antar 'use' import
            'throw', 
            'return',
        ]
    ],
    
    // 4. Memaksa array ditulis rapi (opsional, bagus untuk config module)
    'trim_array_spaces' => true,
    
    // 5. jika file murni PHP (standar PSR)
    // HATI-HATI: Untuk .pdt yang berisi HTML, rule ini biasanya aman karena fixer
    'no_closing_tag' => false, // Set false agar aman untuk template .pdt
])
->setFinder($finder);