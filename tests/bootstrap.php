<?php
declare(strict_types=1);

if (!getenv('TEST_MODE')) {
    echo 'Cannot run tests. Please set TEST_MODE=1' . PHP_EOL;
    exit(-1);
}

/** @var \Composer\Autoload\ClassLoader $autoloader */
$autoloader = require_once __DIR__ . '/../vendor/autoload.php';
$autoloader->addPsr4('HexagonalPlayground\\Tests\\', __DIR__);
