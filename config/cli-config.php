<?php
declare(strict_types=1);
$app = require __DIR__ . '/../src/bootstrap.php';
$container = $app->getContainer();
return \Doctrine\ORM\Tools\Console\ConsoleRunner::createHelperSet($container['doctrine.entityManager']);
