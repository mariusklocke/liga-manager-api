<?php
declare(strict_types=1);

/** @var \Composer\Autoload\ClassLoader $autoloader */
$autoloader = require_once __DIR__ . '/../vendor/autoload.php';
$autoloader->addPsr4('HexagonalPlayground\\Tests\\', __DIR__);

putenv('LOG_LEVEL=warning');
