<?php

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\SimplifiedXmlDriver;
use Doctrine\ORM\Tools\Setup;
use HexagonalDream\Application\FixtureGenerator;
use HexagonalDream\Application\FixtureLoader;
use HexagonalDream\Application\Handler\CreateTeamHandler;
use HexagonalDream\Application\Handler\DeleteTeamHandler;
use HexagonalDream\Application\Repository\MatchRepository;
use HexagonalDream\Application\Repository\RankingRepository;
use HexagonalDream\Application\Repository\SeasonRepository;
use HexagonalDream\Application\Repository\TeamRepository;
use HexagonalDream\Infrastructure\API\Controller\MatchQueryController;
use HexagonalDream\Infrastructure\API\Controller\SeasonQueryController;
use HexagonalDream\Infrastructure\API\Controller\TeamActionController;
use HexagonalDream\Infrastructure\API\Controller\TeamQueryController;
use HexagonalDream\Infrastructure\Persistence\DoctrineObjectPersistence;
use HexagonalDream\Infrastructure\Persistence\SqliteReadDbAdapter;
use HexagonalDream\Infrastructure\Persistence\UuidGenerator;

$container = new \Slim\Container([]);
$container['application.fixtureLoader'] = function() use ($container) {
    return new FixtureLoader(
        $container['infrastructure.persistence.doctrineObjectPersistence'],
        $container['application.fixtureGenerator']
    );
};
$container['application.fixtureGenerator'] = function() use ($container) {
    return new FixtureGenerator($container['infrastructure.persistence.uuidGenerator']);
};
$container['application.handler.CreateTeamHandler'] = function () use ($container) {
    return new CreateTeamHandler(
        $container['infrastructure.persistence.doctrineObjectPersistence'],
        $container['infrastructure.persistence.uuidGenerator']
    );
};
$container['application.handler.DeleteTeamHandler'] = function() use ($container) {
    return new DeleteTeamHandler($container['infrastructure.persistence.doctrineObjectPersistence']);
};
$container['application.repository.team'] = function() use ($container) {
    return new TeamRepository($container['readDbAdapter']);
};
$container['application.repository.season'] = function() use ($container) {
    return new SeasonRepository($container['readDbAdapter']);
};
$container['application.repository.ranking'] = function() use ($container) {
    return new RankingRepository($container['readDbAdapter']);
};
$container['application.repository.match'] = function() use ($container) {
    return new MatchRepository($container['readDbAdapter']);
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
$container['infrastructure.persistence.uuidGenerator'] = function() {
    return new UuidGenerator();
};
$container['infrastructure.persistence.doctrineObjectPersistence'] = function() use ($container) {
    return new DoctrineObjectPersistence($container['doctrine.entityManager']);
};
$container['infrastructure.api.controller.MatchQueryController'] = function() use ($container) {
    return new MatchQueryController($container['application.repository.match']);
};
$container['infrastructure.api.controller.SeasonQueryController'] = function() use ($container) {
    return new SeasonQueryController(
        $container['application.repository.season'],
        $container['application.repository.ranking'],
        $container['application.repository.match']
    );
};
$container['infrastructure.api.controller.TeamQueryController'] = function() use ($container) {
    return new TeamQueryController($container['application.repository.team']);
};
$container['infrastructure.api.controller.TeamActionController'] = function () use ($container) {
    return new TeamActionController(
        $container['application.handler.CreateTeamHandler'],
        $container['application.handler.DeleteTeamHandler']
    );
};
$container['pdo'] = function() {
    return new PDO('sqlite:' . __DIR__ . '/../data/db.sqlite');
};
$container['sqlite'] = function () {
    return new SQLite3(__DIR__ . '/../data/db.sqlite');
};
$container['readDbAdapter'] = function() use ($container) {
    return new SqliteReadDbAdapter($container['sqlite']);
};

return $container;