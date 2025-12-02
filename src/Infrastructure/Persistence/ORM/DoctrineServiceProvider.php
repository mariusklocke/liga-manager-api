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
use HexagonalPlayground\Infrastructure\Config;
use HexagonalPlayground\Infrastructure\Filesystem\Directory;
use HexagonalPlayground\Infrastructure\Filesystem\File;
use HexagonalPlayground\Infrastructure\HealthCheckInterface;
use HexagonalPlayground\Infrastructure\Persistence\ORM\Logging\Middleware as LoggingMiddleware;
use HexagonalPlayground\Infrastructure\Persistence\ORM\Repository\EventRepository;
use HexagonalPlayground\Infrastructure\Persistence\ORM\Repository\MatchDayRepository;
use HexagonalPlayground\Infrastructure\Persistence\ORM\Repository\MatchRepository;
use HexagonalPlayground\Infrastructure\Persistence\ORM\Repository\PitchRepository;
use HexagonalPlayground\Infrastructure\Persistence\ORM\Repository\SeasonRepository;
use HexagonalPlayground\Infrastructure\Persistence\ORM\Repository\TeamRepository;
use HexagonalPlayground\Infrastructure\Persistence\ORM\Repository\TournamentRepository;
use HexagonalPlayground\Infrastructure\Persistence\ORM\Repository\UserRepository;
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
                if ($config->getValue('db.url')) {
                    $url = parse_url($config->getValue('db.url'));
                    $params = [
                        'dbname' => ltrim($url['path'], '/'),
                        'user' => $url['user'],
                        'host' => $url['host'],
                        'driver' => str_replace('-', '_', $url['scheme'])
                    ];
                    if (isset($url['port'])) {
                        $params['port'] = (int)$url['port'];
                    }
                    if (isset($url['pass'])) {
                        $params['password'] = $url['pass'];
                    }
                    $passwordFile = $config->getValue('db.password.file');
                } else {
                    $params = [
                        'dbname' => $config->getValue('mysql.database'),
                        'user' => $config->getValue('mysql.user'),
                        'password' => $config->getValue('mysql.password'),
                        'host' => $config->getValue('mysql.host'),
                        'driver' => 'pdo_mysql'
                    ];
                    $passwordFile = $config->getValue('mysql.password.file');
                }
                if ($params['driver'] === 'pdo_mysql') {
                    // Mysql subclass was introduced with PHP 8.4, constants on PDO class were deprecated with PHP 8.5
                    if (class_exists('\\Pdo\\Mysql')) {
                        $params['driverOptions'] = [\Pdo\Mysql::ATTR_INIT_COMMAND => "SET NAMES utf8"];
                    } else {
                        $params['driverOptions'] = [\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"];
                    }
                }
                if ($passwordFile) {
                    $params['password'] = (new File($passwordFile))->read();
                }
                $customTypes = [
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
                $proxyDir = new Directory(__DIR__, 'Proxy');
                $config = new Configuration();
                $config->setProxyDir($proxyDir->getPath());
                $config->setProxyNamespace(implode('\\', [__NAMESPACE__, 'Proxy']));
                $config->setMetadataDriverImpl($container->get(SimplifiedXmlDriver::class));
                $config->setAutoGenerateProxyClasses(ProxyFactory::AUTOGENERATE_NEVER);
                $config->setMiddlewares([$container->get(LoggingMiddleware::class)]);

                return $config;
            }),

            ObjectManager::class => DI\get(EntityManagerInterface::class),

            SimplifiedXmlDriver::class => DI\factory(function (ContainerInterface $container) {
                $basePath   = (new Directory($container->get('app.home'), 'config', 'doctrine'))->getPath();
                $prefixes   = [
                    (new Directory($basePath, 'Domain'))->getPath() => "HexagonalPlayground\\Domain",
                    (new Directory($basePath, 'Domain', 'Event'))->getPath() => "HexagonalPlayground\\Domain\\Event",
                    (new Directory($basePath, 'Domain', 'Value'))->getPath() => "HexagonalPlayground\\Domain\\Value"
                ];

                $driver = new SimplifiedXmlDriver($prefixes);
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

            HealthCheckInterface::class => DI\add(DI\get(DoctrineHealthCheck::class))
        ];
    }
}
