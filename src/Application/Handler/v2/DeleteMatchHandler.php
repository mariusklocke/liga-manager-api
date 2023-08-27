<?php declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler\v2;

use HexagonalPlayground\Application\Command\v2\DeleteMatchCommand;
use HexagonalPlayground\Application\Handler\AuthAwareHandler;

use HexagonalPlayground\Application\Repository\MatchRepositoryInterface;
use HexagonalPlayground\Application\Security\AuthContext;
use HexagonalPlayground\Domain\Event\Event;
use HexagonalPlayground\Domain\MatchEntity;

class DeleteMatchHandler implements AuthAwareHandler
{
    /** @var MatchRepositoryInterface */
    private MatchRepositoryInterface $matchRepository;

    /**
     * @param MatchRepositoryInterface $matchRepository
     */
    public function __construct(MatchRepositoryInterface $matchRepository)
    {
        $this->matchRepository = $matchRepository;
    }

    /**
     * @param DeleteMatchCommand $command
     * @param AuthContext $authContext
     * @return array|Event[]
     */
    public function __invoke(DeleteMatchCommand $command, AuthContext $authContext): array
    {
        $authContext->getUser()->assertIsAdmin();

        /** @var MatchEntity $match */
        $match = $this->matchRepository->find($command->getId());
        $match->assertDeletable();

        $this->matchRepository->delete($match);

        return [];
    }
}
