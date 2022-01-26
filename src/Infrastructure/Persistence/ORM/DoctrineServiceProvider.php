<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\ORM;

use DI;
use Doctrine\Common\Proxy\AbstractProxyFactory;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Logging\SQLLogger;
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
use HexagonalPlayground\Infrastructure\Environment;
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
use HexagonalPlayground\Infrastructure\Persistence\QueryLogger;
use PDO;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class DoctrineServiceProvider implements ServiceProviderInterface
{
    public function getDefinitions(): array
    {
        return [
            EntityManagerInterface::class => DI\factory(function (ContainerInterface $container) {
                $em = EntityManager::create($container->get(Connection::class), $container->get(Configuration::class));
                $em->getEventManager()->addEventListener(
                    [Events::postLoad],
                    new DoctrineEmbeddableListener($em, $container->get(LoggerInterface::class))
                );
                return $em;
            }),

            Connection::class => DI\factory(function (ContainerInterface $container) {
                $params = [
                    'dbname' => Environment::get('MYSQL_DATABASE'),
                    'user' => Environment::get('MYSQL_USER'),
                    'password' => Environment::get('MYSQL_PASSWORD'),
                    'host' => Environment::get('MYSQL_HOST'),
                    'driver' => 'pdo_mysql',
                    'driverOptions' => [PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"]
                ];

                $connection = DriverManager::getConnection(
                    $params,
                    $container->get(Configuration::class)
                );

                if (!Type::hasType(CustomBinaryType::NAME)) {
                    Type::addType(CustomBinaryType::NAME, CustomBinaryType::class);
                }
                if (!Type::hasType(CustomDateTimeType::NAME)) {
                    Type::addType(CustomDateTimeType::NAME, CustomDateTimeType::class);
                }
                $connection
                    ->getDatabasePlatform()
                    ->registerDoctrineTypeMapping('CustomBinary', CustomBinaryType::NAME);
                $connection
                    ->getDatabasePlatform()
                    ->registerDoctrineTypeMapping('CustomDateTime', CustomDateTimeType::NAME);

                return $connection;
            }),

            Configuration::class => DI\factory(function (ContainerInterface $container) {
                $config = new Configuration();
                $config->setProxyDir(sys_get_temp_dir());
                $config->setProxyNamespace('DoctrineProxies');
                $config->setMetadataDriverImpl($container->get(SimplifiedXmlDriver::class));
                $config->setSQLLogger($container->get(SQLLogger::class));
                $config->setAutoGenerateProxyClasses(AbstractProxyFactory::AUTOGENERATE_FILE_NOT_EXISTS);

                return $config;
            }),

            SQLLogger::class => DI\get(QueryLogger::class),

            ObjectManager::class => DI\get(EntityManagerInterface::class),

            SimplifiedXmlDriver::class => DI\factory(function () {
                $basePath = Environment::get('APP_HOME');
                $driver = new SimplifiedXmlDriver([
                    $basePath . "/config/doctrine/Infrastructure/API/Security/WebAuthn"
                    => "HexagonalPlayground\\Infrastructure\\API\\Security\\WebAuthn",
                    $basePath . "/config/doctrine/Domain"
                    => "HexagonalPlayground\\Domain",
                    $basePath . "/config/doctrine/Domain/Event"
                    => "HexagonalPlayground\\Domain\\Event",
                    $basePath . "/config/doctrine/Domain/Value"
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
