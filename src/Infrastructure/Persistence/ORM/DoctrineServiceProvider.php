<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\ORM;

use DI;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\Driver\SimplifiedXmlDriver;
use Doctrine\ORM\Proxy\ProxyFactory;
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
use HexagonalPlayground\Infrastructure\Filesystem\FilesystemService;
use HexagonalPlayground\Infrastructure\HealthCheckInterface;
use HexagonalPlayground\Infrastructure\Persistence\ORM\Logging\Middleware as LoggingMiddleware;
use HexagonalPlayground\Infrastructure\Persistence\ORM\Repository\EventRepository;
use HexagonalPlayground\Infrastructure\Persistence\ORM\Repository\MatchDayRepository;
use HexagonalPlayground\Infrastructure\Persistence\ORM\Repository\MatchRepository;
use HexagonalPlayground\Infrastructure\Persistence\ORM\Repository\PitchRepository;
use HexagonalPlayground\Infrastructure\Persistence\ORM\Repository\PublicKeyCredentialRepository;
use HexagonalPlayground\Infrastructure\Persistence\ORM\Repository\SeasonRepository;
use HexagonalPlayground\Infrastructure\Persistence\ORM\Repository\TeamRepository;
use HexagonalPlayground\Infrastructure\Persistence\ORM\Repository\TournamentRepository;
use HexagonalPlayground\Infrastructure\Persistence\ORM\Repository\UserRepository;
use PDO;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Throwable;

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
                /** @var LoggerInterface $logger */
                $logger = $container->get(LoggerInterface::class);
                /** @var Configuration $doctrineConfig */
                $doctrineConfig = $container->get(Configuration::class);
                /** @var Config $config */
                $config = $container->get(Config::class);
                $params = [
                    'dbname' => $config->getValue('mysql.database'),
                    'user' => $config->getValue('mysql.user'),
                    'password' => $config->getValue('mysql.password'),
                    'host' => $config->getValue('mysql.host'),
                    'driver' => 'pdo_mysql'
                ];
                if ($params['driver'] === 'pdo_mysql') {
                    $params['driverOptions'] = [PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"];
                }
                if ($config->getValue('mysql.password.file')) {
                    /** @var FilesystemService $filesystem */
                    $filesystem = $container->get(FilesystemService::class);
                    $params['password'] = $filesystem->getFileContents($config->getValue('mysql.password.file'));
                }
                $customTypes = [
                    CustomBinaryType::class => [
                        'dbType' => 'CustomBinary',
                        'doctrineType' => CustomBinaryType::NAME
                    ],
                    CustomDateTimeType::class => [
                        'dbType' => 'CustomDateTime',
                        'doctrineType' => CustomDateTimeType::NAME
                    ],
                ];
                $attempt = 1;
                $timeout = 60;
                $startedAt = time();

                do {
                    try {
                        $connection = DriverManager::getConnection($params, $doctrineConfig);
                        $platform   = $connection->getDatabasePlatform();
                    } catch (Throwable $exception) {
                        $connection = null;
                        $platform = null;
                        $logger->warning($exception->getMessage(), ['host' => $params['host'], 'attempt' => $attempt]);
                        sleep(5);
                        if (time() - $startedAt < $timeout) {
                            $attempt++;
                        } else {
                            throw $exception;
                        }
                    }
                } while ($connection === null || $platform === null);

                $logger->debug('Connected to database', ['version' => $connection->getServerVersion()]);

                foreach ($customTypes as $className => $definition) {
                    if (!Type::hasType($definition['doctrineType'])) {
                        Type::addType($definition['doctrineType'], $className);
                    }
                    $platform->registerDoctrineTypeMapping($definition['dbType'], $definition['doctrineType']);
                }

                return $connection;
            }),

            Configuration::class => DI\factory(function (ContainerInterface $container) {
                /** @var FilesystemService $filesystem */
                $filesystem = $container->get(FilesystemService::class);
                $config = new Configuration();
                $config->setProxyDir($$filesystem->joinPaths([__DIR__, 'Proxy']));
                $config->setProxyNamespace(implode('\\', [__NAMESPACE__, 'Proxy']));
                $config->setMetadataDriverImpl($container->get(SimplifiedXmlDriver::class));
                $config->setAutoGenerateProxyClasses(ProxyFactory::AUTOGENERATE_NEVER);
                $config->setMiddlewares([$container->get(LoggingMiddleware::class)]);

                return $config;
            }),

            ObjectManager::class => DI\get(EntityManagerInterface::class),

            SimplifiedXmlDriver::class => DI\factory(function (ContainerInterface $container) {
                /** @var FilesystemService $filesystem */
                $filesystem = $container->get(FilesystemService::class);
                $basePath   = $filesystem->joinPaths([$container->get('app.home'), 'config', 'doctrine']);
                $driver     = new SimplifiedXmlDriver([
                    $filesystem->joinPaths([$basePath, 'Infrastructure', 'API', 'Security', 'WebAuthn'])
                    => "HexagonalPlayground\\Infrastructure\\API\\Security\\WebAuthn",
                    $filesystem->joinPaths([$basePath, 'Domain'])
                    => "HexagonalPlayground\\Domain",
                    $filesystem->joinPaths([$basePath, 'Domain', 'Event'])
                    => "HexagonalPlayground\\Domain\\Event",
                    $filesystem->joinPaths([$basePath, 'Domain', 'Value'])
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
