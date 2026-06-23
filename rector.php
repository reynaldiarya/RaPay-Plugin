<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;
use Rector\ValueObject\PhpVersion;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/indobe-for-woocommerce.php',
        __DIR__ . '/bank',
        __DIR__ . '/blocks',
        __DIR__ . '/e-money',
    ])
    ->withBootstrapFiles([
        __DIR__ . '/vendor/php-stubs/wordpress-stubs/wordpress-stubs.php',
        __DIR__ . '/vendor/php-stubs/woocommerce-stubs/woocommerce-stubs.php',
    ])
    ->withPhpVersion(PhpVersion::PHP_81)
    ->withSets([
        LevelSetList::UP_TO_PHP_84,
    ])
    ->withPreparedSets(
        deadCode: false,
        codeQuality: true,
        codingStyle: true,
        typeDeclarations: true,
    );