#!/usr/bin/env php
<?php
declare(strict_types=1);

$tools = [
    "https://getcomposer.org/composer.phar" => __DIR__ . '/../composer.phar',
    "https://phar.phpunit.de/phpunit-8.0.4.phar" => __DIR__ . '/../phpunit.phar.bak',
    "https://raw.github.com/mamuz/PhpDependencyAnalysis/master/download/phpda.pubkey" => __DIR__ . '/../phpda.phar.pubkey',
    "https://raw.github.com/mamuz/PhpDependencyAnalysis/master/download/phpda" => __DIR__ . '/../phpda.phar'
];
foreach ($tools as $source => $target) {
    if (!copy($source, $target)) {
        throw new RuntimeException('Could not download file from "' . $source . '"');
    }
}