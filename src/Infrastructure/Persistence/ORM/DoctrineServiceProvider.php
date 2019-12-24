<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\ORM;

use DI;
use Doctrine\ORM\EntityManagerInterface;
use HexagonalPlayground\Application\OrmTransactionWrapperInterface;
use HexagonalPlayground\Application\Repository\MatchDayRepositoryInterface;
use HexagonalPlayground\Application\Repository\MatchRepositoryInterface;
use HexagonalPlayground\Application\Repository\PitchRepositoryInterface;
use HexagonalPlayground\Application\Repository\SeasonRepositoryInterface;
use HexagonalPlayground\Application\Repository\TeamRepositoryInterface;
use HexagonalPlayground\Application\Repository\TournamentRepositoryInterface;
use HexagonalPlayground\Application\Security\UserRepositoryInterface;
use HexagonalPlayground\Application\ServiceProviderInterface;
use HexagonalPlayground\Domain\Match;
use HexagonalPlayground\Domain\MatchDay;
use HexagonalPlayground\Domain\Pitch;
use HexagonalPlayground\Domain\Season;
use HexagonalPlayground\Domain\Team;
use HexagonalPlayground\Domain\Tournament;
use HexagonalPlayground\Domain\User;
use HexagonalPlayground\Infrastructure\API\Security\WebAuthn\PublicKeyCredential;
use HexagonalPlayground\Infrastructure\API\Security\WebAuthn\PublicKeyCredentialSourceRepository;
use Psr\Log\LoggerInterface;

class DoctrineServiceProvider implements ServiceProviderInterface
{
    public function getDefinitions(): array
    {
        return [
            EntityManagerInterface::class => DI\factory(EntityManagerFactory::class)
                ->parameter('logger', DI\get(LoggerInterface::class)),

            OrmTransactionWrapperInterface::class => DI\get(DoctrineTransactionWrapper::class),

            DoctrineTransactionWrapper::class => DI\autowire(),

            UserRepositoryInterface::class => DI\factory([EntityManagerInterface::class, 'getRepository'])
                ->parameter('entityName', User::class),

            TeamRepositoryInterface::class => DI\factory([EntityManagerInterface::class, 'getRepository'])
                ->parameter('entityName', Team::class),

            MatchRepositoryInterface::class => DI\factory([EntityManagerInterface::class, 'getRepository'])
                ->parameter('entityName', Match::class),

            SeasonRepositoryInterface::class => DI\factory([EntityManagerInterface::class, 'getRepository'])
                ->parameter('entityName', Season::class),

            TournamentRepositoryInterface::class => DI\factory([EntityManagerInterface::class, 'getRepository'])
                ->parameter('entityName', Tournament::class),

            PitchRepositoryInterface::class => DI\factory([EntityManagerInterface::class, 'getRepository'])
                ->parameter('entityName', Pitch::class),

            MatchDayRepositoryInterface::class => DI\factory([EntityManagerInterface::class, 'getRepository'])
                ->parameter('entityName', MatchDay::class),

            PublicKeyCredentialSourceRepository::class => DI\factory([EntityManagerInterface::class, 'getRepository'])
                ->parameter('entityName', PublicKeyCredential::class),
        ];
    }
}