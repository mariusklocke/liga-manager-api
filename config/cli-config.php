<?php
$app = require __DIR__ . '/../src/bootstrap.php';
$container = $app->getContainer();
return \Doctrine\ORM\Tools\Console\ConsoleRunner::createHelperSet($container['doctrine.entityManager']);
