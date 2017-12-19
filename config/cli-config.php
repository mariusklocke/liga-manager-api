<?php
$container = require __DIR__ . '/../src/bootstrap.php';
return \Doctrine\ORM\Tools\Console\ConsoleRunner::createHelperSet($container['doctrine.entityManager']);
