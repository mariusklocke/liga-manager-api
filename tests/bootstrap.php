<?php
declare(strict_types=1);

if (!getenv('ALLOW_TESTS')) {
    echo "Running tests is not allowed in this environment. Please set ALLOW_TESTS=1";
    exit(1);
}

require __DIR__ . '/../vendor/autoload.php';
