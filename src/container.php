<?php
declare(strict_types=1);

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\Driver\SimplifiedXmlDriver;
use Doctrine\ORM\Tools\Setup;
use HexagonalPlayground\Application\Command\AddTeamToSeasonCommand;
use HexagonalPlayground\Application\Command\CreateSeasonCommand;
use HexagonalPlayground\Application\Command\RemoveTeamFromSeasonCommand;
use HexagonalPlayground\Application\Factory\SeasonFactory;
use HexagonalPlayground\Application\Handler\AddTeamToSeasonHandler;
use HexagonalPlayground\Application\Handler\CreateSeasonHandler;
use HexagonalPlayground\Application\Handler\RemoveTeamFromSeasonHandler;
use HexagonalPlayground\Infrastructure\API\ErrorHandler;
use HexagonalPlayground\Infrastructure\Persistence\DoctrineEmbeddableListener;
use HexagonalPlayground\Application\Handler\LocateMatchHandler;
use HexagonalPlayground\Application\Handler\SubmitMatchResultHandler;
use HexagonalPlayground\Application\Bus\BatchCommandBus;
use HexagonalPlayground\Application\Command\CancelMatchCommand;
use HexagonalPlayground\Application\Command\CreateMatchesForSeasonCommand;
use HexagonalPlayground\Application\Command\CreateSingleMatchCommand;
use HexagonalPlayground\Application\Command\CreateTeamCommand;
use HexagonalPlayground\Application\Command\DeleteSeasonCommand;
use HexagonalPlayground\Application\Command\DeleteTeamCommand;
use HexagonalPlayground\Application\Command\LocateMatchCommand;
use HexagonalPlayground\Application\Command\ScheduleMatchCommand;
use HexagonalPlayground\Application\Command\StartSeasonCommand;
use HexagonalPlayground\Application\Bus\SingleCommandBus;
use HexagonalPlayground\Application\Command\SubmitMatchResultCommand;
use HexagonalPlayground\Application\FixtureGenerator;
use HexagonalPlayground\Application\FixtureLoader;
use HexagonalPlayground\Application\Handler\CancelMatchHandler;
use HexagonalPlayground\Application\Handler\CreateMatchesForSeasonHandler;
use HexagonalPlayground\Application\Handler\CreateSingleMatchHandler;
use HexagonalPlayground\Application\Handler\CreateTeamHandler;
use HexagonalPlayground\Application\Handler\DeleteSeasonHandler;
use HexagonalPlayground\Application\Handler\DeleteTeamHandler;
use HexagonalPlayground\Application\Handler\ScheduleMatchHandler;
use HexagonalPlayground\Application\Handler\StartSeasonHandler;
use HexagonalPlayground\Application\Repository\MatchRepository;
use HexagonalPlayground\Application\Repository\PitchRepository;
use HexagonalPlayground\Application\Repository\RankingRepository;
use HexagonalPlayground\Application\Repository\SeasonRepository;
use HexagonalPlayground\Application\Repository\TeamRepository;
use HexagonalPlayground\Application\Factory\MatchFactory;
use HexagonalPlayground\Application\UuidGenerator;
use HexagonalPlayground\Infrastructure\API\Controller\MatchCommandController;
use HexagonalPlayground\Infrastructure\API\Controller\MatchQueryController;
use HexagonalPlayground\Infrastructure\API\Controller\PitchQueryController;
use HexagonalPlayground\Infrastructure\API\Controller\SeasonCommandController;
use HexagonalPlayground\Infrastructure\API\Controller\SeasonQueryController;
use HexagonalPlayground\Infrastructure\API\Controller\TeamCommandController;
use HexagonalPlayground\Infrastructure\API\Controller\TeamQueryController;
use HexagonalPlayground\Infrastructure\Persistence\DoctrineObjectPersistence;
use HexagonalPlayground\Infrastructure\Persistence\DoctrineQueryLogger;
use HexagonalPlayground\Infrastructure\Persistence\SqliteReadDbAdapter;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Ramsey\Uuid\UuidFactory as RamseyUuidFactory;

$container = new \Slim\Container([]);

$container[SeasonFactory::class] = function () use ($container) {
    return new SeasonFactory($container['uuidGenerator'], function() {
        return new \Doctrine\Common\Collections\ArrayCollection();
    });
};
$container[FixtureLoader::class] = function() use ($container) {
    return new FixtureLoader(
        $container['objectPersistence'],
        new FixtureGenerator(
            $container['uuidGenerator'],
            $container[SeasonFactory::class]
        )
    );
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
$container[ScheduleMatchCommand::class] = function() use ($container) {
    return new ScheduleMatchHandler($container['objectPersistence']);
};
$container[SubmitMatchResultCommand::class] = function () use ($container) {
    return new SubmitMatchResultHandler($container['objectPersistence']);
};
$container[LocateMatchCommand::class] = function () use ($container) {
    return new LocateMatchHandler($container['objectPersistence']);
};
$container[CancelMatchCommand::class] = function () use ($container) {
    return new CancelMatchHandler($container['objectPersistence']);
};
$container[AddTeamToSeasonCommand::class] = function () use ($container) {
    return new AddTeamToSeasonHandler($container['objectPersistence']);
};
$container[RemoveTeamFromSeasonCommand::class] = function () use ($container) {
    return new RemoveTeamFromSeasonHandler($container['objectPersistence']);
};
$container[CreateSeasonCommand::class] = function () use ($container) {
    return new CreateSeasonHandler($container['objectPersistence'], $container[SeasonFactory::class]);
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
    $em = EntityManager::create($container['doctrine.connection'], $container['doctrine.config']);
    $em->getEventManager()->addEventListener(
        [Events::postLoad],
        new DoctrineEmbeddableListener($em, $container['logger'])
    );
    return $em;
};
$container['doctrine.connection'] = function() use ($container) {
    return DriverManager::getConnection(['pdo' => $container['pdo']], $container['doctrine.config']);
};
$container['doctrine.config'] = function() use ($container) {
    $config = Setup::createConfiguration(true);
    $driver = new SimplifiedXmlDriver([__DIR__ . "/../config/doctrine" => "HexagonalPlayground\\Domain"]);
    $driver->setGlobalBasename('global');
    $config->setMetadataDriverImpl($driver);
    $config->setSQLLogger(new DoctrineQueryLogger($container['logger']));
    return $config;
};
$container['uuidGenerator'] = function() {
    return new UuidGenerator(new RamseyUuidFactory());
};
$container['objectPersistence'] = function() use ($container) {
    return new DoctrineObjectPersistence($container['doctrine.entityManager']);
};
$container[MatchCommandController::class] = function() use ($container) {
    return new MatchCommandController($container['commandBus']);
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
    $db = new SqliteReadDbAdapter($container['sqlite']);
    $db->setLogger($container['logger']);
    return $db;
};
$container['commandBus'] = function() use ($container) {
    return new SingleCommandBus($container, $container['objectPersistence']);
};
$container['batchCommandBus'] = function () use ($container) {
    return new BatchCommandBus($container, $container['objectPersistence']);
};
$container['logger'] = function() {
    return new Logger('logger', [new StreamHandler('php://stdout')]);
};
$container['errorHandler'] = function() use ($container) {
    return new ErrorHandler($container['logger']);
};
unset($container['phpErrorHandler']);
unset($container['notAllowedHandler']);
unset($container['notFoundHandler']);

return $container;