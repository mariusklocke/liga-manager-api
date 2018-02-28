<?php
declare(strict_types=1);
$container = require __DIR__ . '/../src/container.php';
return \Doctrine\ORM\Tools\Console\ConsoleRunner::createHelperSet($container['doctrine.entityManager']);
