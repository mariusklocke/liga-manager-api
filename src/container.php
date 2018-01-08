<?php

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\SimplifiedXmlDriver;
use Doctrine\ORM\Tools\Setup;
use HexagonalDream\Application\Bus\BatchCommandBus;
use HexagonalDream\Application\Command\CreateMatchesForSeasonCommand;
use HexagonalDream\Application\Command\CreateSingleMatchCommand;
use HexagonalDream\Application\Command\CreateTeamCommand;
use HexagonalDream\Application\Command\DeleteSeasonCommand;
use HexagonalDream\Application\Command\DeleteTeamCommand;
use HexagonalDream\Application\Command\StartSeasonCommand;
use HexagonalDream\Application\Bus\SingleCommandBus;
use HexagonalDream\Application\FixtureGenerator;
use HexagonalDream\Application\FixtureLoader;
use HexagonalDream\Application\Handler\CreateMatchesForSeasonHandler;
use HexagonalDream\Application\Handler\CreateSingleMatchHandler;
use HexagonalDream\Application\Handler\CreateTeamHandler;
use HexagonalDream\Application\Handler\DeleteSeasonHandler;
use HexagonalDream\Application\Handler\DeleteTeamHandler;
use HexagonalDream\Application\Handler\StartSeasonHandler;
use HexagonalDream\Application\Repository\MatchRepository;
use HexagonalDream\Application\Repository\PitchRepository;
use HexagonalDream\Application\Repository\RankingRepository;
use HexagonalDream\Application\Repository\SeasonRepository;
use HexagonalDream\Application\Repository\TeamRepository;
use HexagonalDream\Application\Factory\MatchFactory;
use HexagonalDream\Application\UuidGenerator;
use HexagonalDream\Infrastructure\API\Controller\MatchQueryController;
use HexagonalDream\Infrastructure\API\Controller\PitchQueryController;
use HexagonalDream\Infrastructure\API\Controller\SeasonCommandController;
use HexagonalDream\Infrastructure\API\Controller\SeasonQueryController;
use HexagonalDream\Infrastructure\API\Controller\TeamCommandController;
use HexagonalDream\Infrastructure\API\Controller\TeamQueryController;
use HexagonalDream\Infrastructure\API\InternalErrorHandler;
use HexagonalDream\Infrastructure\API\MethodNotAllowedHandler;
use HexagonalDream\Infrastructure\API\NotFoundErrorHandler;
use HexagonalDream\Infrastructure\Persistence\DoctrineObjectPersistence;
use HexagonalDream\Infrastructure\Persistence\SqliteReadDbAdapter;
use Ramsey\Uuid\UuidFactory;

$container = new \Slim\Container([]);

$container[FixtureLoader::class] = function() use ($container) {
    return new FixtureLoader($container['objectPersistence'], new FixtureGenerator($container['uuidGenerator']));
};
$container[CreateTeamCommand::class] = function () use ($container) {
    return new CreateTeamHandler($container['objectPersistence'], $container['uuidGenerator']);
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
$container[CreateMatchesForSeasonCommand::class] = function() use ($container) {
    return new CreateMatchesForSeasonHandler(
        $container['objectPersistence'],
        new MatchFactory($container['uuidGenerator'])
    );
};
$container[CreateSingleMatchCommand::class] = function() use ($container) {
    return new CreateSingleMatchHandler($container['objectPersistence'], $container['uuidGenerator']);
};
$container[DeleteSeasonCommand::class] = function() use ($container) {
    return new DeleteSeasonHandler($container['objectPersistence']);
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
    return new UuidGenerator(new UuidFactory());
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
$container[SeasonCommandController::class] = function() use ($container) {
    return new SeasonCommandController($container['commandBus']);
};
$container[TeamQueryController::class] = function() use ($container) {
    return new TeamQueryController($container[TeamRepository::class]);
};
$container[TeamCommandController::class] = function () use ($container) {
    return new TeamCommandController($container['commandBus']);
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
$container['commandBus'] = function() use ($container) {
    return new SingleCommandBus($container, $container['objectPersistence']);
};
$container['batchCommandBus'] = function () use ($container) {
    return new BatchCommandBus($container, $container['objectPersistence']);
};
$container['notAllowedHandler'] = function() use ($container) {
    return new MethodNotAllowedHandler();
};
$container['notFoundHandler'] = function() use ($container) {
    return new NotFoundErrorHandler();
};
$container['errorHandler'] = $container['phpErrorHandler'] = function() use ($container) {
    return new InternalErrorHandler(true);
};

return $container;