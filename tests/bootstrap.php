<?php
declare(strict_types=1);

/** @var \Composer\Autoload\ClassLoader $autoloader */
$autoloader = require_once __DIR__ . '/../vendor/autoload.php';
$autoloader->addPsr4('HexagonalPlayground\\Tests\\', __DIR__);

putenv('SQLITE_PATH=' . tempnam(sys_get_temp_dir(), 'sqlite'));
putenv('LOG_LEVEL=warning');
register_shutdown_function(function() {
    unlink(getenv('SQLITE_PATH'));
});