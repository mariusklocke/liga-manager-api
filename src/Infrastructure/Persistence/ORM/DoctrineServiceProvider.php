<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\ORM;

use DI;
use Doctrine\ORM\EntityManagerInterface;
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
use HexagonalPlayground\Infrastructure\HealthCheckInterface;
use HexagonalPlayground\Infrastructure\Persistence\ORM\Repository\MatchDayRepository;
use HexagonalPlayground\Infrastructure\Persistence\ORM\Repository\MatchRepository;
use HexagonalPlayground\Infrastructure\Persistence\ORM\Repository\PitchRepository;
use HexagonalPlayground\Infrastructure\Persistence\ORM\Repository\PublicKeyCredentialRepository;
use HexagonalPlayground\Infrastructure\Persistence\ORM\Repository\SeasonRepository;
use HexagonalPlayground\Infrastructure\Persistence\ORM\Repository\TeamRepository;
use HexagonalPlayground\Infrastructure\Persistence\ORM\Repository\TournamentRepository;
use HexagonalPlayground\Infrastructure\Persistence\ORM\Repository\UserRepository;
use Psr\Log\LoggerInterface;

class DoctrineServiceProvider implements ServiceProviderInterface
{
    public function getDefinitions(): array
    {
        return [
            EntityManagerInterface::class => DI\factory(EntityManagerFactory::class)
                ->parameter('logger', DI\get(LoggerInterface::class)),

            ObjectManager::class => DI\get(EntityManagerInterface::class),

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