<?php
declare(strict_types=1);

if (!getenv('ALLOW_TESTS')) {
    echo "Running tests is not allowed in this environment. Please set ALLOW_TESTS=1";
    exit(1);
}

/** @var \Composer\Autoload\ClassLoader $autoloader */
$autoloader = require_once __DIR__ . '/../vendor/autoload.php';
$autoloader->addPsr4('HexagonalPlayground\\Tests\\', __DIR__);

# Make sure we have a clean database
system('lima app:setup:db -n');
