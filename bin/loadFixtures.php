<?php
$app = require __DIR__ . '/../src/bootstrap.php';
$container = $app->getContainer();
$container[\HexagonalDream\Application\FixtureLoader::class]();