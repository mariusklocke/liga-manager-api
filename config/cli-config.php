<?php
declare(strict_types=1);

use Doctrine\ORM\Tools\Console\ConsoleRunner;

require_once __DIR__ . '/../vendor/autoload.php';

$container = \HexagonalPlayground\Infrastructure\ContainerBuilder::build();
return ConsoleRunner::createHelperSet($container->get(\Doctrine\ORM\EntityManager::class));
