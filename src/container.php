<?php

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\SimplifiedXmlDriver;
use Doctrine\ORM\Tools\Setup;
use HexagonalDream\Application\Command\CreateTeamCommand;
use HexagonalDream\Application\Command\DeleteTeamCommand;
use HexagonalDream\Application\Command\StartSeasonCommand;
use HexagonalDream\Application\FixtureGenerator;
use HexagonalDream\Application\FixtureLoader;
use HexagonalDream\Application\Handler\CreateTeamHandler;
use HexagonalDream\Application\Handler\DeleteTeamHandler;
use HexagonalDream\Application\Handler\StartSeasonHandler;
use HexagonalDream\Application\Repository\MatchRepository;
use HexagonalDream\Application\Repository\PitchRepository;
use HexagonalDream\Application\Repository\RankingRepository;
use HexagonalDream\Application\Repository\SeasonRepository;
use HexagonalDream\Application\Repository\TeamRepository;
use HexagonalDream\Infrastructure\API\Controller\MatchQueryController;
use HexagonalDream\Infrastructure\API\Controller\PitchQueryController;
use HexagonalDream\Infrastructure\API\Controller\SeasonQueryController;
use HexagonalDream\Infrastructure\API\Controller\TeamActionController;
use HexagonalDream\Infrastructure\API\Controller\TeamQueryController;
use HexagonalDream\Infrastructure\Persistence\DoctrineObjectPersistence;
use HexagonalDream\Infrastructure\Persistence\SqliteReadDbAdapter;
use HexagonalDream\Infrastructure\Persistence\UuidGenerator;

$container = new \Slim\Container([]);

$container[FixtureLoader::class] = function() use ($container) {
    return new FixtureLoader(
        $container['objectPersistence'],
        new FixtureGenerator($container['uuidGenerator'])
    );
};
$container[CreateTeamCommand::class] = function () use ($container) {
    return new CreateTeamHandler(
        $container['objectPersistence'],
        $container['uuidGenerator']
    );
};
$container[DeleteTeamCommand::class] = function() use ($container) {
    return new DeleteTeamHandler($container['objectPersistence']);
};
$container[StartSeasonCommand::class] = function() use ($container) {
    return new StartSeasonHandler(
        $container['objectPersistence'],
        function() {
            return new \Doctrine\Common\Collections\ArrayCollection();
        }
    );
};
$container[TeamRepository::class] = function() use ($container) {
    return new TeamRepository($container['readDbAdapter']);
};
$container[SeasonRepository::class] = function() use ($container) {
    return new SeasonRepository($container['readDbAdapter']);
};
$container[RankingRepository::class] = function() use ($container) {
    return new RankingRepository($container['readDbAdapter']);
};
$container[PitchRepository::class] = function() use ($container) {
    return new PitchRepository($container['readDbAdapter']);
};
$container[MatchRepository::class] = function() use ($container) {
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
$container['uuidGenerator'] = function() {
    return new UuidGenerator();
};
$container['objectPersistence'] = function() use ($container) {
    return new DoctrineObjectPersistence($container['doctrine.entityManager']);
};
$container[MatchQueryController::class] = function() use ($container) {
    return new MatchQueryController($container[MatchRepository::class]);
};
$container[PitchQueryController::class] = function() use ($container) {
    return new PitchQueryController($container[PitchRepository::class]);
};
$container[SeasonQueryController::class] = function() use ($container) {
    return new SeasonQueryController(
        $container[SeasonRepository::class],
        $container[RankingRepository::class],
        $container[MatchRepository::class]
    );
};
$container[TeamQueryController::class] = function() use ($container) {
    return new TeamQueryController($container[TeamRepository::class]);
};
$container[TeamActionController::class] = function () use ($container) {
    return new TeamActionController(
        $container[CreateTeamCommand::class],
        $container[DeleteTeamCommand::class]
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