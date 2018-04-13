<?php
declare(strict_types=1);
$container = require __DIR__ . '/../config/container.php';
return \Doctrine\ORM\Tools\Console\ConsoleRunner::createHelperSet($container['doctrine.entityManager']);
