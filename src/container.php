<?php
declare(strict_types=1);

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\Driver\SimplifiedXmlDriver;
use Doctrine\ORM\Tools\Setup;
use HexagonalPlayground\Application\Command\AddTeamToSeasonCommand;
use HexagonalPlayground\Application\Command\ChangeUserPasswordCommand;
use HexagonalPlayground\Application\Command\CreateSeasonCommand;
use HexagonalPlayground\Application\Command\CreateTournamentCommand;
use HexagonalPlayground\Application\Command\CreateUserCommand;
use HexagonalPlayground\Application\Command\RemoveTeamFromSeasonCommand;
use HexagonalPlayground\Application\Command\SetTournamentRoundCommand;
use HexagonalPlayground\Application\Factory\SeasonFactory;
use HexagonalPlayground\Application\Factory\TournamentFactory;
use HexagonalPlayground\Application\Factory\UserFactory;
use HexagonalPlayground\Application\Handler\AddTeamToSeasonHandler;
use HexagonalPlayground\Application\Handler\ChangeUserPasswordHandler;
use HexagonalPlayground\Application\Handler\CreateSeasonHandler;
use HexagonalPlayground\Application\Handler\CreateTournamentHandler;
use HexagonalPlayground\Application\Handler\CreateUserHandler;
use HexagonalPlayground\Application\Handler\RemoveTeamFromSeasonHandler;
use HexagonalPlayground\Application\Handler\SetTournamentRoundHandler;
use HexagonalPlayground\Application\Repository\TournamentRepository;
use HexagonalPlayground\Application\Security\Authenticator;
use HexagonalPlayground\Application\Security\TokenFactoryInterface;
use HexagonalPlayground\Domain\User;
use HexagonalPlayground\Application\Security\UserRepositoryInterface;
use HexagonalPlayground\Infrastructure\API\Controller\TournamentCommandController;
use HexagonalPlayground\Infrastructure\API\Controller\TournamentQueryController;
use HexagonalPlayground\Infrastructure\API\Controller\UserCommandController;
use HexagonalPlayground\Infrastructure\API\Controller\UserQueryController;
use HexagonalPlayground\Infrastructure\API\ErrorHandler;
use HexagonalPlayground\Infrastructure\API\Security\JsonWebTokenFactory;
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
use HexagonalPlayground\Infrastructure\API\Controller\MatchCommandController;
use HexagonalPlayground\Infrastructure\API\Controller\MatchQueryController;
use HexagonalPlayground\Infrastructure\API\Controller\PitchQueryController;
use HexagonalPlayground\Infrastructure\API\Controller\SeasonCommandController;
use HexagonalPlayground\Infrastructure\API\Controller\SeasonQueryController;
use HexagonalPlayground\Infrastructure\API\Controller\TeamCommandController;
use HexagonalPlayground\Infrastructure\API\Controller\TeamQueryController;
use HexagonalPlayground\Infrastructure\Persistence\DoctrineObjectPersistence;
use HexagonalPlayground\Infrastructure\Persistence\DoctrineQueryLogger;
use HexagonalPlayground\Infrastructure\Persistence\MysqliReadDbAdapter;
use HexagonalPlayground\Infrastructure\Persistence\UuidGenerator;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Ramsey\Uuid\UuidFactory as RamseyUuidFactory;

if (file_exists(__DIR__ . '/../.env')) {
    (new Dotenv\Dotenv(__DIR__ . '/../'))->load();
}

$container = new \Slim\Container([]);

$container[SeasonFactory::class] = function () use ($container) {
    return new SeasonFactory($container['uuidGenerator'], function() {
        return new \Doctrine\Common\Collections\ArrayCollection();
    });
};
$container[TournamentFactory::class] = function () use ($container) {
    return new TournamentFactory($container['uuidGenerator'], function() {
        return new \Doctrine\Common\Collections\ArrayCollection();
    });
};
$container[MatchFactory::class] = function () use ($container) {
    return new MatchFactory($container['uuidGenerator']);
};
$container[UserFactory::class] = function () use ($container) {
    return new UserFactory($container['uuidGenerator'], function() {
        return new \Doctrine\Common\Collections\ArrayCollection();
    });
};
$container[FixtureLoader::class] = function() use ($container) {
    return new FixtureLoader(
        $container['objectPersistence'],
        new FixtureGenerator(
            $container['uuidGenerator'],
            $container[SeasonFactory::class],
            $container[UserFactory::class]
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
    return new CreateMatchesForSeasonHandler($container['objectPersistence'], $container[MatchFactory::class]);
};
$container[CreateSingleMatchCommand::class] = function() use ($container) {
    return new CreateSingleMatchHandler($container['objectPersistence'], $container[MatchFactory::class]);
};
$container[DeleteSeasonCommand::class] = function() use ($container) {
    return new DeleteSeasonHandler($container['objectPersistence']);
};
$container[ScheduleMatchCommand::class] = function() use ($container) {
    return new ScheduleMatchHandler($container['objectPersistence']);
};
$container[SubmitMatchResultCommand::class] = function () use ($container) {
    return new SubmitMatchResultHandler($container['objectPersistence'], $container[Authenticator::class]);
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
$container[CreateTournamentCommand::class] = function () use ($container) {
    return new CreateTournamentHandler($container['objectPersistence'], $container[TournamentFactory::class]);
};
$container[SetTournamentRoundCommand::class] = function () use ($container) {
    return new SetTournamentRoundHandler($container['objectPersistence'], $container[MatchFactory::class]);
};
$container[ChangeUserPasswordCommand::class] = function () use ($container) {
    return new ChangeUserPasswordHandler($container[Authenticator::class]);
};
$container[CreateUserCommand::class] = function () use ($container) {
    return new CreateUserHandler($container[UserRepositoryInterface::class], $container[UserFactory::class]);
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
$container[TournamentRepository::class] = function () use ($container) {
    return new TournamentRepository($container['readDbAdapter']);
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
    $config->setSQLLogger($container['doctrine.queryLogger']);
    return $config;
};
$container['doctrine.queryLogger'] = function () use ($container) {
    return new DoctrineQueryLogger($container['logger']);
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
$container[TournamentCommandController::class] = function () use ($container) {
    return new TournamentCommandController($container['commandBus']);
};
$container[TournamentQueryController::class] = function () use ($container) {
    return new TournamentQueryController($container[TournamentRepository::class]);
};
$container[UserCommandController::class] = function () use ($container) {
    return new UserCommandController($container['commandBus']);
};
$container[UserQueryController::class] = function () use ($container) {
    return new UserQueryController($container[Authenticator::class]);
};
$container['pdo'] = function() {
    $mysql = new PDO(
        'mysql:host=' . getenv('MYSQL_HOST') . ';dbname=' . getenv('MYSQL_DATABASE'),
        getenv('MYSQL_USER'),
        getenv('MYSQL_PASSWORD'),
        [PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"]
    );
    return $mysql;
};
$container['readDbAdapter'] = function() use ($container) {
    $mysqli = new mysqli(
        getenv('MYSQL_HOST'),
        getenv('MYSQL_USER'),
        getenv('MYSQL_PASSWORD'),
        getenv('MYSQL_DATABASE')
    );
    $mysqli->set_charset('utf8');
    $db = new MysqliReadDbAdapter($mysqli);
    $db->setLogger($container['doctrine.queryLogger']);
    return $db;
};
$container['commandBus'] = function() use ($container) {
    return new SingleCommandBus($container, $container['objectPersistence']);
};
$container['batchCommandBus'] = function () use ($container) {
    return new BatchCommandBus($container, $container['objectPersistence']);
};
$container[TokenFactoryInterface::class] = function () {
    return new JsonWebTokenFactory();
};
$container[Authenticator::class] = function () use ($container) {
    /** @var EntityManager $em */
    $em = $container['doctrine.entityManager'];
    /** @var UserRepositoryInterface $userRepository */
    $userRepository = $em->getRepository(User::class);
    return new Authenticator($container[TokenFactoryInterface::class], $userRepository);
};
$container[UserRepositoryInterface::class] = function () use ($container) {
    /** @var EntityManager $em */
    $em = $container['doctrine.entityManager'];
    return $em->getRepository(User::class);
};
$container['logger'] = function() {
    if ($path = getenv('LOG_PATH')) {
        if (strpos($path, '/') !== 0) {
            // Make path relative to application root
            $path = __DIR__ . '/../' . $path;
        }
    }
    if (php_sapi_name() === 'cli') {
        putenv('LOG_STREAM=php://stdout');
    }
    $stream = $path ?: (getenv('LOG_STREAM') ?: 'php://stdout');
    $level = Logger::toMonologLevel(getenv('LOG_LEVEL') ?: 'warning');
    $handler = new StreamHandler($stream, $level);
    return new Logger('logger', [$handler]);
};
$container['errorHandler'] = function() use ($container) {
    return new ErrorHandler($container['logger']);
};
unset($container['phpErrorHandler']);
unset($container['notAllowedHandler']);
unset($container['notFoundHandler']);

return $container;