<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\ORM;

use DI;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Connection;
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
use HexagonalPlayground\Infrastructure\Filesystem\Directory;
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

            Connection::class => DI\factory(new ConnectionFactory()),

            Configuration::class => DI\factory(function (ContainerInterface $container) {
                $config = new Configuration();
                $config->setMetadataDriverImpl($container->get(SimplifiedXmlDriver::class));
                $config->setMiddlewares([$container->get(LoggingMiddleware::class)]);
                $config->enableNativeLazyObjects(true);

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
