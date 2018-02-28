<?php
declare(strict_types=1);

ini_set('display_errors', 'Off');
// For PHP's internal webserver
$filename = __DIR__.preg_replace('#(\?.*)$#', '', $_SERVER['REQUEST_URI']);
if (php_sapi_name() === 'cli-server' && is_file($filename)) {
    return false;
}

require_once __DIR__ . '/../vendor/autoload.php';
\HexagonalPlayground\Infrastructure\API\Bootstrap::bootstrap()->run();