<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\RemoveTeamFromSeasonCommand;
use HexagonalPlayground\Application\Exception\NotFoundException;
use HexagonalPlayground\Application\Permission\IsAdmin;
use HexagonalPlayground\Application\Repository\SeasonRepositoryInterface;
use HexagonalPlayground\Application\Repository\TeamRepositoryInterface;
use HexagonalPlayground\Application\Security\AuthContext;
use HexagonalPlayground\Domain\Event\Event;
use HexagonalPlayground\Domain\Season;
use HexagonalPlayground\Domain\Team;

class RemoveTeamFromSeasonHandler implements AuthAwareHandler
{
    /** @var SeasonRepositoryInterface */
    private $seasonRepository;

    /** @var TeamRepositoryInterface */
    private $teamRepository;

    /**
     * @param SeasonRepositoryInterface $seasonRepository
     * @param TeamRepositoryInterface $teamRepository
     */
    public function __construct(SeasonRepositoryInterface $seasonRepository, TeamRepositoryInterface $teamRepository)
    {
        $this->seasonRepository = $seasonRepository;
        $this->teamRepository = $teamRepository;
    }

    /**
     * @param RemoveTeamFromSeasonCommand $command
     * @param AuthContext $authContext
     * @return array|Event[]
     * @throws NotFoundException
     */
    public function __invoke(RemoveTeamFromSeasonCommand $command, AuthContext $authContext): array
    {
        $isAdmin = new IsAdmin($authContext->getUser());
        $isAdmin->check();

        /** @var Season $season */
        $season = $this->seasonRepository->find($command->getSeasonId());
        /** @var Team $team */
        $team   = $this->teamRepository->find($command->getTeamId());

        $season->removeTeam($team);

        return [];
    }
}
