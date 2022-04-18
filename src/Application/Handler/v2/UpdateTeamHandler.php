<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler\v2;

use HexagonalPlayground\Application\Command\v2\UpdateTeamCommand;
use HexagonalPlayground\Application\Handler\AuthAwareHandler;
use HexagonalPlayground\Application\Repository\TeamRepositoryInterface;
use HexagonalPlayground\Application\Security\AuthContext;
use HexagonalPlayground\Domain\Team;

class UpdateTeamHandler implements AuthAwareHandler
{
    private TeamRepositoryInterface $teamRepository;

    /**
     * @param TeamRepositoryInterface $teamRepository
     */
    public function __construct(TeamRepositoryInterface $teamRepository)
    {
        $this->teamRepository = $teamRepository;
    }

    public function __invoke(UpdateTeamCommand $command, AuthContext $authContext): array
    {
        /** @var Team $team */
        $team = $this->teamRepository->find($command->getId());
        $team->setName($command->getName());
        $team->setContact($command->getContact());

        $this->teamRepository->save($team);

        return [];
    }
}
