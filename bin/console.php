<?php

require __DIR__ . '/../vendor/autoload.php';
$container = require __DIR__ . '/../src/container.php';

$app = new \Symfony\Component\Console\Application();
$app->setHelperSet(\Doctrine\ORM\Tools\Console\ConsoleRunner::createHelperSet($container['doctrine.entityManager']));
$app->setCatchExceptions(true);

// Add Doctrine commands
\Doctrine\ORM\Tools\Console\ConsoleRunner::addCommands($app);
// Add own command
$app->add(new \HexagonalPlayground\Infrastructure\CLI\ImportMatchesCommand($container['batchCommandBus']));
$app->add(new \HexagonalPlayground\Infrastructure\CLI\LoadFixturesCommand($container[\HexagonalPlayground\Application\FixtureLoader::class]));

$app->run();