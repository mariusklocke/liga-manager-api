<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler\v2;

use HexagonalPlayground\Application\Command\v2\DeleteTournamentCommand;
use HexagonalPlayground\Application\Handler\AuthAwareHandler;
use HexagonalPlayground\Application\Permission\IsAdmin;
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
        $isAdmin = new IsAdmin($authContext->getUser());
        $isAdmin->check();

        /** @var Tournament $tournament */
        $tournament = $this->repository->find($command->getId());
        $tournament->clearMatches();
        $this->repository->delete($tournament);

        return [];
    }
}
