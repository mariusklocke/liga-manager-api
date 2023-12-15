<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\ORM;

use DI;
use Doctrine\Common\EventManager;
use Doctrine\Common\Proxy\AbstractProxyFactory;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Logging\Middleware as LoggingMiddleware;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\Driver\SimplifiedXmlDriver;
use Doctrine\Persistence\ObjectManager;
use HexagonalPlayground\Application\OrmTransactionWrapperInterface;
use HexagonalPlayground\Application\Repository\EventRepositoryInterface;
use HexagonalPlayground\Application\Repository\MatchDayRepositoryInterface;
use HexagonalPlayground\Application\Repository\MatchRepositoryInterface;
use HexagonalPlayground\Application\Repository\PitchRepositoryInterface;
use HexagonalPlayground\Application\Repository\SeasonRepositoryInterface;
use HexagonalPlayground\Application\Repository\TeamRepositoryInterface;
use HexagonalPlayground\Application\Repository\TournamentRepositoryInterface;
use HexagonalPlayground\Application\Security\UserRepositoryInterface;
use HexagonalPlayground\Application\ServiceProviderInterface;
use HexagonalPlayground\Infrastructure\API\Security\WebAuthn\PublicKeyCredentialSourceRepository;
use HexagonalPlayground\Infrastructure\Config;
use HexagonalPlayground\Infrastructure\HealthCheckInterface;
use HexagonalPlayground\Infrastructure\Persistence\ORM\Repository\EventRepository;
use HexagonalPlayground\Infrastructure\Persistence\ORM\Repository\MatchDayRepository;
use HexagonalPlayground\Infrastructure\Persistence\ORM\Repository\MatchRepository;
use HexagonalPlayground\Infrastructure\Persistence\ORM\Repository\PitchRepository;
use HexagonalPlayground\Infrastructure\Persistence\ORM\Repository\PublicKeyCredentialRepository;
use HexagonalPlayground\Infrastructure\Persistence\ORM\Repository\SeasonRepository;
use HexagonalPlayground\Infrastructure\Persistence\ORM\Repository\TeamRepository;
use HexagonalPlayground\Infrastructure\Persistence\ORM\Repository\TournamentRepository;
use HexagonalPlayground\Infrastructure\Persistence\ORM\Repository\UserRepository;
use HexagonalPlayground\Infrastructure\Retry;
use PDO;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class DoctrineServiceProvider implements ServiceProviderInterface
{
    public function getDefinitions(): array
    {
        return [
            EntityManagerInterface::class => DI\factory(function (ContainerInterface $container) {
                $eventManager  = new EventManager();
                $entityManager = new EntityManager(
                    $container->get(Connection::class),
                    $container->get(Configuration::class),
                    $eventManager
                );

                $eventManager->addEventListener(
                    [Events::postLoad],
                    new DoctrineEmbeddableListener($entityManager, $container->get(LoggerInterface::class))
                );

                return $entityManager;
            }),

            Connection::class => DI\factory(function (ContainerInterface $container) {
                /** @var Config $config */
                $config = $container->get(Config::class);

                $params = [
                    'dbname' => $config->mysqlDatabase,
                    'user' => $config->mysqlUser,
                    'password' => $config->mysqlPassword,
                    'host' => $config->mysqlHost,
                    'driver' => 'pdo_mysql',
                    'driverOptions' => [PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"]
                ];

                $connection = DriverManager::getConnection($params, $container->get(Configuration::class));

                if (!Type::hasType(CustomBinaryType::NAME)) {
                    Type::addType(CustomBinaryType::NAME, CustomBinaryType::class);
                }
                if (!Type::hasType(CustomDateTimeType::NAME)) {
                    Type::addType(CustomDateTimeType::NAME, CustomDateTimeType::class);
                }

                $retry = new Retry($container->get(LoggerInterface::class), 60, 5);

                /** @var AbstractPlatform $platform */
                $platform = $retry(function () use ($connection) {
                    return $connection->getDatabasePlatform();
                });

                $platform->registerDoctrineTypeMapping('CustomBinary', CustomBinaryType::NAME);
                $platform->registerDoctrineTypeMapping('CustomDateTime', CustomDateTimeType::NAME);

                return $connection;
            }),

            Configuration::class => DI\factory(function (ContainerInterface $container) {
                /** @var Config $appConfig */
                $appConfig = $container->get(Config::class);

                $config = new Configuration();
                $config->setProxyDir(sys_get_temp_dir());
                $config->setProxyNamespace('DoctrineProxies');
                $config->setMetadataDriverImpl($container->get(SimplifiedXmlDriver::class));
                $config->setAutoGenerateProxyClasses(AbstractProxyFactory::AUTOGENERATE_FILE_NOT_EXISTS);

                if ($appConfig->logLevel === 'debug') {
                    $config->setMiddlewares([new LoggingMiddleware($container->get(LoggerInterface::class))]);
                }

                return $config;
            }),

            ObjectManager::class => DI\get(EntityManagerInterface::class),

            SimplifiedXmlDriver::class => DI\factory(function (ContainerInterface $container) {
                $basePath = join(
                    DIRECTORY_SEPARATOR,
                    [$container->get('app.home'), 'config', 'doctrine']
                );
                $driver = new SimplifiedXmlDriver([
                    join(DIRECTORY_SEPARATOR, [$basePath, 'Infrastructure', 'API', 'Security', 'WebAuthn'])
                    => "HexagonalPlayground\\Infrastructure\\API\\Security\\WebAuthn",
                    join(DIRECTORY_SEPARATOR, [$basePath, 'Domain'])
                    => "HexagonalPlayground\\Domain",
                    join(DIRECTORY_SEPARATOR, [$basePath, 'Domain', 'Event'])
                    => "HexagonalPlayground\\Domain\\Event",
                    join(DIRECTORY_SEPARATOR, [$basePath, 'Domain', 'Value'])
                    => "HexagonalPlayground\\Domain\\Value"
                ]);
                $driver->setGlobalBasename('global');

                return $driver;
            }),

            OrmTransactionWrapperInterface::class => DI\get(DoctrineTransactionWrapper::class),

            EventRepositoryInterface::class => DI\get(EventRepository::class),
            MatchRepositoryInterface::class => DI\get(MatchRepository::class),
            MatchDayRepositoryInterface::class => DI\get(MatchDayRepository::class),
            PitchRepositoryInterface::class => DI\get(PitchRepository::class),
            SeasonRepositoryInterface::class => DI\get(SeasonRepository::class),
            TeamRepositoryInterface::class => DI\get(TeamRepository::class),
            TournamentRepositoryInterface::class => DI\get(TournamentRepository::class),
            UserRepositoryInterface::class => DI\get(UserRepository::class),
            PublicKeyCredentialSourceRepository::class => DI\get(PublicKeyCredentialRepository::class),

            HealthCheckInterface::class => DI\add(DI\get(DoctrineHealthCheck::class))
        ];
    }
}
