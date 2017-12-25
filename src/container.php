<?php

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\SimplifiedXmlDriver;
use Doctrine\ORM\Tools\Setup;
use HexagonalDream\Application\FixtureLoader;
use HexagonalDream\Framework\Persistence\DoctrineObjectPersistence;
use HexagonalDream\Framework\Persistence\PdoReadDbAdapter;
use HexagonalDream\Framework\Persistence\UuidGenerator;

$container = new \Pimple\Container();

$container['application.fixtureLoader'] = function() use ($container) {
    return new FixtureLoader(
        $container['framework.persistence.doctrineObjectPersistence'],
        $container['framework.persistence.uuidGenerator']
    );
};
$container['doctrine.entityManager'] = function() use ($container) {
    return EntityManager::create($container['doctrine.connection'], $container['doctrine.config']);
};
$container['doctrine.connection'] = function() use ($container) {
    return DriverManager::getConnection(['pdo' => $container['pdo']], $container['doctrine.config']);
};
$container['doctrine.config'] = function() {
    $config = Setup::createConfiguration(true);
    $driver = new SimplifiedXmlDriver([__DIR__ . "/../config/doctrine" => "HexagonalDream\\Domain"]);
    $driver->setGlobalBasename('global');
    $config->setMetadataDriverImpl($driver);
    return $config;
};
$container['framework.persistence.pdoReadDbAdapter'] = function() use ($container) {
    return new PdoReadDbAdapter($container['pdo']);
};
$container['framework.persistence.uuidGenerator'] = function() {
    return new UuidGenerator();
};
$container['framework.persistence.doctrineObjectPersistence'] = function() use ($container) {
    return new DoctrineObjectPersistence($container['doctrine.entityManager']);
};
$container['pdo'] = function() {
    return new PDO('sqlite:' . __DIR__ . '/../data/db.sqlite');
};

return $container;