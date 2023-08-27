<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\DeleteTournamentCommand;
use HexagonalPlayground\Application\Repository\TournamentRepositoryInterface;
use HexagonalPlayground\Application\Security\AuthContext;
use HexagonalPlayground\Domain\Event\Event;
use HexagonalPlayground\Domain\Tournament;

class DeleteTournamentHandler implements AuthAwareHandler
{
    /** @var TournamentRepositoryInterface */
    private TournamentRepositoryInterface $repository;

    /**
     * @param TournamentRepositoryInterface $repository
     */
    public function __construct(TournamentRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param DeleteTournamentCommand $command
     * @param AuthContext $authContext
     * @return array|Event[]
     */
    public function __invoke(DeleteTournamentCommand $command, AuthContext $authContext): array
    {
        $authContext->getUser()->assertIsAdmin();

        /** @var Tournament $tournament */
        $tournament = $this->repository->find($command->getTournamentId());
        $tournament->clearMatches();
        $this->repository->delete($tournament);

        return [];
    }
}
