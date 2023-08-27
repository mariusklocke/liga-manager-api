<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler\v2;

use HexagonalPlayground\Application\Command\v2\CreateTeamCommand;
use HexagonalPlayground\Application\Handler\AuthAwareHandler;
use HexagonalPlayground\Application\Repository\TeamRepositoryInterface;
use HexagonalPlayground\Application\Security\AuthContext;
use HexagonalPlayground\Domain\Event\Event;
use HexagonalPlayground\Domain\Team;

class CreateTeamHandler implements AuthAwareHandler
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
     * @param CreateTeamCommand $command
     * @param AuthContext $authContext
     * @return array|Event[]
     */
    public function __invoke(CreateTeamCommand $command, AuthContext $authContext): array
    {
        $events = [];

        $authContext->getUser()->assertIsAdmin();

        $team = new Team($command->getId(), $command->getName(), $command->getContact());
        $this->teamRepository->save($team);

        $events[] = new Event('team:created', [
            'teamId' => $team->getId()
        ]);

        return $events;
    }
}
