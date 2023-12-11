<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

putenv("APP_HOME=" . realpath(join(DIRECTORY_SEPARATOR, [__DIR__ , '..'])));
putenv("APP_VERSION=development");

switch (php_sapi_name()) {
    case 'fpm-fcgi':
        $app = new HexagonalPlayground\Infrastructure\API\Application();
        $app->run();
        break;
    case 'cli':
        $app = new HexagonalPlayground\Infrastructure\CLI\Application();
        $app->run();
        break;
    default:
        throw new Exception('Unsupported PHP SAPI');
}
