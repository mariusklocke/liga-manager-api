<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler\v2;

use HexagonalPlayground\Application\Command\v2\DeleteTeamCommand;
use HexagonalPlayground\Application\Handler\AuthAwareHandler;
use HexagonalPlayground\Application\Repository\TeamRepositoryInterface;
use HexagonalPlayground\Application\Security\AuthContext;
use HexagonalPlayground\Domain\Event\Event;
use HexagonalPlayground\Domain\Team;

class DeleteTeamHandler implements AuthAwareHandler
{
    /** @var TeamRepositoryInterface */
    private TeamRepositoryInterface $teamRepository;

    /**
     * @param TeamRepositoryInterface $teamRepository
     */
    public function __construct(TeamRepositoryInterface $teamRepository)
    {
        $this->teamRepository = $teamRepository;
    }

    /**
     * @param DeleteTeamCommand $command
     * @param AuthContext $authContext
     * @return array|Event[]
     */
    public function __invoke(DeleteTeamCommand $command, AuthContext $authContext): array
    {
        $authContext->getUser()->assertIsAdmin();

        /** @var Team $team */
        $team = $this->teamRepository->find($command->getId());
        $this->teamRepository->delete($team);

        return [];
    }
}
