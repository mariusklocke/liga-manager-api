<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\DeleteTeamCommand;
use HexagonalPlayground\Domain\Exception\NotFoundException;
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
     * @throws NotFoundException
     */
    public function __invoke(DeleteTeamCommand $command, AuthContext $authContext): array
    {
        $authContext->getUser()->assertIsAdmin();

        /** @var Team $team */
        $team = $this->teamRepository->find($command->getTeamId());
        $this->teamRepository->delete($team);

        return [];
    }
}
