<?php
$app = require __DIR__ . '/../src/bootstrap.php';
$container = $app->getContainer();
$container['application.fixtureLoader']();