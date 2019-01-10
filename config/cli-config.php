<?php
declare(strict_types=1);

use Doctrine\ORM\Tools\Console\ConsoleRunner;

require_once __DIR__ . '/../vendor/autoload.php';

$container = \HexagonalPlayground\Infrastructure\CLI\Bootstrap::createContainer();
return ConsoleRunner::createHelperSet($container[\Doctrine\ORM\EntityManager::class]);
