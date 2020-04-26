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
use Doctrine\ORM\Tools\Setup;
use Doctrine\Persistence\ObjectManager;
use HexagonalPlayground\Application\OrmTransactionWrapperInterface;
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

            PDO::class => DI\factory(function () {
                $dsn = sprintf(
                    'mysql:host=%s;dbname=%s',
                    Environment::get('MYSQL_HOST'),
                    Environment::get('MYSQL_DATABASE')
                );

                return new PDO(
                    $dsn,
                    Environment::get('MYSQL_USER'),
                    Environment::get('MYSQL_PASSWORD'),
                    [PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"]
                );
            }),

            Connection::class => DI\factory(function (ContainerInterface $container) {
                $connection = DriverManager::getConnection(
                    ['pdo' => $container->get(PDO::class)],
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
                $config = Setup::createConfiguration(false);
                $config->setMetadataDriverImpl($container->get(SimplifiedXmlDriver::class));
                $config->setSQLLogger($container->get(SQLLogger::class));
                $config->setAutoGenerateProxyClasses(AbstractProxyFactory::AUTOGENERATE_FILE_NOT_EXISTS);

                return $config;
            }),

            SQLLogger::class => DI\get(QueryLogger::class),

            ObjectManager::class => DI\get(EntityManagerInterface::class),

            SimplifiedXmlDriver::class => DI\factory(function () {
                $driver = new SimplifiedXmlDriver([
                    Environment::get('APP_HOME') . "/config/doctrine/Infrastructure/API/Security/WebAuthn"
                    => "HexagonalPlayground\\Infrastructure\\API\\Security\\WebAuthn",
                    Environment::get('APP_HOME') . "/config/doctrine/Domain"
                    => "HexagonalPlayground\\Domain",
                    Environment::get('APP_HOME') . "/config/doctrine/Domain/Event"
                    => "HexagonalPlayground\\Domain\\Event",
                    Environment::get('APP_HOME') . "/config/doctrine/Domain/Value"
                    => "HexagonalPlayground\\Domain\\Value"
                ]);
                $driver->setGlobalBasename('global');

                return $driver;
            }),

            OrmTransactionWrapperInterface::class => DI\get(DoctrineTransactionWrapper::class),

            DoctrineTransactionWrapper::class => DI\autowire(),

            MatchRepositoryInterface::class => DI\get(MatchRepository::class),
            MatchRepository::class => DI\autowire(),

            MatchDayRepositoryInterface::class => DI\get(MatchDayRepository::class),
            MatchDayRepository::class => DI\autowire(),

            PitchRepositoryInterface::class => DI\get(PitchRepository::class),
            PitchRepository::class => DI\autowire(),

            SeasonRepositoryInterface::class => DI\get(SeasonRepository::class),
            SeasonRepository::class => DI\autowire(),

            TeamRepositoryInterface::class => DI\get(TeamRepository::class),
            TeamRepository::class => DI\autowire(),

            TournamentRepositoryInterface::class => DI\get(TournamentRepository::class),
            TournamentRepository::class => DI\autowire(),

            UserRepositoryInterface::class => DI\get(UserRepository::class),
            UserRepository::class => DI\autowire(),

            PublicKeyCredentialSourceRepository::class => DI\get(PublicKeyCredentialRepository::class),
            PublicKeyCredentialRepository::class => DI\autowire(),

            HealthCheckInterface::class => DI\add(DI\get(DoctrineHealthCheck::class))
        ];
    }
}