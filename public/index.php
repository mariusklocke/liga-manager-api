<?php
declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

$app = new HexagonalPlayground\Infrastructure\API\Application();
$app->run();
