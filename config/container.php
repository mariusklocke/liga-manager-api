<?php
declare(strict_types=1);

use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\Driver\SimplifiedXmlDriver;
use Doctrine\ORM\Tools\Setup;
use HexagonalPlayground\Application\Bus\HandlerResolver;
use HexagonalPlayground\Application\Command\AddTeamToSeasonCommand;
use HexagonalPlayground\Application\Command\ChangeUserPasswordCommand;
use HexagonalPlayground\Application\Command\CreatePitchCommand;
use HexagonalPlayground\Application\Command\CreateSeasonCommand;
use HexagonalPlayground\Application\Command\CreateTournamentCommand;
use HexagonalPlayground\Application\Command\CreateUserCommand;
use HexagonalPlayground\Application\Command\LoadFixturesCommand;
use HexagonalPlayground\Application\Command\RemoveTeamFromSeasonCommand;
use HexagonalPlayground\Application\Command\SetTournamentRoundCommand;
use HexagonalPlayground\Application\Command\UpdatePitchContactCommand;
use HexagonalPlayground\Application\Command\UpdateTeamContactCommand;
use HexagonalPlayground\Application\Email\MailerInterface;
use HexagonalPlayground\Application\EventStoreInterface;
use HexagonalPlayground\Application\Handler\AddTeamToSeasonHandler;
use HexagonalPlayground\Application\Handler\ChangeUserPasswordHandler;
use HexagonalPlayground\Application\Handler\CreatePitchHandler;
use HexagonalPlayground\Application\Handler\CreateSeasonHandler;
use HexagonalPlayground\Application\Handler\CreateTournamentHandler;
use HexagonalPlayground\Application\Handler\CreateUserHandler;
use HexagonalPlayground\Application\Handler\LoadFixturesHandler;
use HexagonalPlayground\Application\Handler\RemoveTeamFromSeasonHandler;
use HexagonalPlayground\Application\Handler\SetTournamentRoundHandler;
use HexagonalPlayground\Application\Handler\UpdatePitchContactHandler;
use HexagonalPlayground\Application\Handler\UpdateTeamContactHandler;
use HexagonalPlayground\Application\OrmTransactionWrapperInterface;
use HexagonalPlayground\Application\Repository\TournamentRepository;
use HexagonalPlayground\Application\Security\Authenticator;
use HexagonalPlayground\Application\Security\TokenFactoryInterface;
use HexagonalPlayground\Domain\Match;
use HexagonalPlayground\Domain\Pitch;
use HexagonalPlayground\Domain\Season;
use HexagonalPlayground\Domain\Team;
use HexagonalPlayground\Domain\Tournament;
use HexagonalPlayground\Domain\User;
use HexagonalPlayground\Infrastructure\API\Controller\PitchCommandController;
use HexagonalPlayground\Infrastructure\API\Controller\TournamentCommandController;
use HexagonalPlayground\Infrastructure\API\Controller\TournamentQueryController;
use HexagonalPlayground\Infrastructure\API\Controller\UserCommandController;
use HexagonalPlayground\Infrastructure\API\Controller\UserQueryController;
use HexagonalPlayground\Infrastructure\API\ErrorHandler;
use HexagonalPlayground\Infrastructure\API\Routing\RemoveTrailingSlash;
use HexagonalPlayground\Infrastructure\API\Security\JsonWebTokenFactory;
use HexagonalPlayground\Infrastructure\CommandHandlerResolver;
use HexagonalPlayground\Infrastructure\Email\SwiftMailer;
use HexagonalPlayground\Infrastructure\Persistence\ORM\BaseRepository;
use HexagonalPlayground\Infrastructure\Persistence\ORM\DoctrineTransactionWrapper;
use HexagonalPlayground\Infrastructure\Persistence\ORM\DoctrineEmbeddableListener;
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
use HexagonalPlayground\Infrastructure\Persistence\ORM\DoctrineQueryLogger;
use HexagonalPlayground\Infrastructure\Persistence\MysqliReadDbAdapter;
use HexagonalPlayground\Infrastructure\TemplateRenderer;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Slim\Http\Request;

$container = new \Slim\Container([]);

$container[MatchFactory::class] = function () use ($container) {
    return new MatchFactory();
};
$container[CreateTeamCommand::class] = function () use ($container) {
    return new CreateTeamHandler(
        $container['orm.repository.team']
    );
};
$container[DeleteTeamCommand::class] = function() use ($container) {
    return new DeleteTeamHandler($container['orm.repository.team']);
};
$container[StartSeasonCommand::class] = function() use ($container) {
    return new StartSeasonHandler($container['orm.repository.season']);
};
$container[CreateMatchesForSeasonCommand::class] = function() use ($container) {
    return new CreateMatchesForSeasonHandler(
        $container[MatchFactory::class],
        $container['orm.repository.season'],
        $container['orm.repository.match']
    );
};
$container[CreateSingleMatchCommand::class] = function() use ($container) {
    return new CreateSingleMatchHandler(
        $container[MatchFactory::class],
        $container['orm.repository.match'],
        $container['orm.repository.team'],
        $container['orm.repository.season']
    );
};
$container[DeleteSeasonCommand::class] = function() use ($container) {
    return new DeleteSeasonHandler($container['orm.repository.season']);
};
$container[ScheduleMatchCommand::class] = function() use ($container) {
    return new ScheduleMatchHandler($container['orm.repository.match']);
};
$container[SubmitMatchResultCommand::class] = function () use ($container) {
    return new SubmitMatchResultHandler(
        $container['orm.repository.match'],
        $container[Authenticator::class]
    );
};
$container[LocateMatchCommand::class] = function () use ($container) {
    return new LocateMatchHandler(
        $container['orm.repository.match'],
        $container['orm.repository.pitch']
    );
};
$container[CancelMatchCommand::class] = function () use ($container) {
    return new CancelMatchHandler($container['orm.repository.match']);
};
$container[AddTeamToSeasonCommand::class] = function () use ($container) {
    return new AddTeamToSeasonHandler(
        $container['orm.repository.season'],
        $container['orm.repository.team']
    );
};
$container[RemoveTeamFromSeasonCommand::class] = function () use ($container) {
    return new RemoveTeamFromSeasonHandler(
        $container['orm.repository.season'],
        $container['orm.repository.team']
    );
};
$container[CreateSeasonCommand::class] = function () use ($container) {
    return new CreateSeasonHandler($container['orm.repository.season']);
};
$container[CreateTournamentCommand::class] = function () use ($container) {
    return new CreateTournamentHandler($container['orm.repository.tournament']);
};
$container[SetTournamentRoundCommand::class] = function () use ($container) {
    return new SetTournamentRoundHandler(
        $container[MatchFactory::class],
        $container['orm.repository.tournament'],
        $container['orm.repository.match'],
        $container['orm.repository.team']
    );
};
$container[ChangeUserPasswordCommand::class] = function () use ($container) {
    return new ChangeUserPasswordHandler($container[Authenticator::class]);
};
$container[CreateUserCommand::class] = function () use ($container) {
    return new CreateUserHandler(
        $container['orm.repository.user'],
        $container['orm.repository.team'],
        $container[Authenticator::class]
    );
};
$container[CreatePitchCommand::class] = function () use ($container) {
    return new CreatePitchHandler(
        $container['orm.repository.pitch']
    );
};
$container[UpdateTeamContactCommand::class] = function () use ($container) {
    return new UpdateTeamContactHandler($container['orm.repository.team']);
};
$container[UpdatePitchContactCommand::class] = function () use ($container) {
    return new UpdatePitchContactHandler($container['orm.repository.pitch']);
};
$container[LoadFixturesCommand::class] = function () use ($container) {
    return new LoadFixturesHandler(
        $container['orm.repository.team'],
        $container['orm.repository.season'],
        $container['orm.repository.pitch'],
        $container['orm.repository.user'],
        new FixtureGenerator()
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
    $isDevMode = (getenv('APP_ENVIRONMENT') === 'development');
    $config = Setup::createConfiguration($isDevMode);
    $driver = new SimplifiedXmlDriver([__DIR__ . "/../config/doctrine" => "HexagonalPlayground\\Domain"]);
    $driver->setGlobalBasename('global');
    $config->setMetadataDriverImpl($driver);
    $config->setSQLLogger($container['doctrine.queryLogger']);
    $config->setDefaultRepositoryClassName(BaseRepository::class);
    return $config;
};
$container['doctrine.queryLogger'] = function () use ($container) {
    return new DoctrineQueryLogger($container['logger']);
};
$container[OrmTransactionWrapperInterface::class] = function() use ($container) {
    return new DoctrineTransactionWrapper($container['doctrine.entityManager']);
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
$container[PitchCommandController::class] = function () use ($container) {
    return new PitchCommandController($container['commandBus']);
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
$container['cli.command'] = [
    'app:load-fixtures' => function () use ($container) {
        return new \HexagonalPlayground\Infrastructure\CLI\LoadFixturesCommand($container['commandBus']);
    },
    'app:create-user' => function () use ($container) {
        return new \HexagonalPlayground\Infrastructure\CLI\CreateUserCommand($container['commandBus']);
    },
    'app:import-matches' => function () use ($container) {
        return new \HexagonalPlayground\Infrastructure\CLI\ImportMatchesCommand($container['batchCommandBus']);
    },
    'app:reset-password' => function () use ($container) {
        return new \HexagonalPlayground\Infrastructure\CLI\ResetPasswordCommand(
            $container[MailerInterface::class],
            $container[TemplateRenderer::class]
        );
    },
    'app:list-events' => function () use ($container) {
        return new \HexagonalPlayground\Infrastructure\CLI\ListEventsCommand($container[EventStoreInterface::class]);
    }
];
$container[EventStoreInterface::class] = function () use ($container) {
    $client = new \MongoDB\Client('mongodb://' . getenv('MONGO_HOST'));
    $db = $client->{getenv('MONGO_DATABASE')};
    return new \HexagonalPlayground\Infrastructure\Persistence\MongoEventStore($db->events);
};
$container['orm.repository.user'] = function () use ($container) {
    return $container['doctrine.entityManager']->getRepository(User::class);
};
$container['orm.repository.team'] = function () use ($container) {
    return $container['doctrine.entityManager']->getRepository(Team::class);
};
$container['orm.repository.match'] = function () use ($container) {
    return $container['doctrine.entityManager']->getRepository(Match::class);
};
$container['orm.repository.season'] = function () use ($container) {
    return $container['doctrine.entityManager']->getRepository(Season::class);
};
$container['orm.repository.tournament'] = function () use ($container) {
    return $container['doctrine.entityManager']->getRepository(Tournament::class);
};
$container['orm.repository.pitch'] = function () use ($container) {
    return $container['doctrine.entityManager']->getRepository(Pitch::class);
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
$container[HandlerResolver::class] = function () use ($container) {
    return new CommandHandlerResolver($container);
};
$container['commandBus'] = function() use ($container) {
    return new SingleCommandBus($container[HandlerResolver::class], $container[OrmTransactionWrapperInterface::class]);
};
$container['batchCommandBus'] = function () use ($container) {
    return new BatchCommandBus($container[HandlerResolver::class], $container[OrmTransactionWrapperInterface::class]);
};
$container[MailerInterface::class] = function () use ($container) {
    $transport = new Swift_SmtpTransport(getenv('SMTP_HOST'), getenv('SMTP_PORT'));
    list($senderAddress, $senderName) = explode(';', getenv('EMAIL_SENDER'));
    return new SwiftMailer(
        new Swift_Mailer($transport),
        $senderAddress,
        $senderName
    );
};
$container[TemplateRenderer::class] = function () use ($container) {
    return new TemplateRenderer(__DIR__ . '/../templates');
};
$container[TokenFactoryInterface::class] = function () {
    return new JsonWebTokenFactory();
};
$container[Authenticator::class] = function () use ($container) {
    return new Authenticator($container[TokenFactoryInterface::class], $container['orm.repository.user']);
};
$container['request'] = function ($container) {
    /** @var Request $request */
    $request = (new RemoveTrailingSlash())->__invoke(Request::createFromEnvironment($container['environment']));
    return $request;
};
$container['logger'] = function() {
    $stream = null;
    if ($path = getenv('LOG_PATH')) {
        if (strpos($path, '/') !== 0) {
            // Make path relative to application root
            $path = __DIR__ . '/../' . $path;
        }
        $stream = fopen($path, 'a');
    }
    if (!is_resource($stream)) {
        $path = 'php://stdout';
        if (php_sapi_name() !== 'cli') {
            $path = getenv('LOG_STREAM') ?: $path;
        }
        $stream = fopen($path, 'a');
    }
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