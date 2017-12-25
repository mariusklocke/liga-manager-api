<?php
/**
 * installPharTools.php
 *
 * @author    Marius Klocke <marius.klocke@eventim.de>
 * @copyright Copyright (c) 2017, CTS EVENTIM Solutions GmbH
 */

$tools = [
    "https://getcomposer.org/download/1.5.6/composer.phar" => __DIR__ . '/../composer.phar',
    "https://phar.phpunit.de/phpunit-6.5.5.phar" => __DIR__ . '/../phpunit.phar',
    "https://raw.github.com/mamuz/PhpDependencyAnalysis/master/download/phpda.pubkey" => __DIR__ . '/../phpda.phar.pubkey',
    "https://raw.github.com/mamuz/PhpDependencyAnalysis/master/download/phpda" => __DIR__ . '/../phpda.phar'
];
foreach ($tools as $source => $target) {
    if (!copy($source, $target)) {
        throw new RuntimeException('Could not download file from "' . $source . '"');
    }
}