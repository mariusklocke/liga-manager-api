<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\RemoveTeamFromSeasonCommand;
use HexagonalPlayground\Application\Exception\NotFoundException;
use HexagonalPlayground\Application\Permission\IsAdmin;
use HexagonalPlayground\Application\Repository\SeasonRepositoryInterface;
use HexagonalPlayground\Application\Repository\TeamRepositoryInterface;
use HexagonalPlayground\Application\Security\AuthContext;

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
     * @throws NotFoundException
     */
    public function __invoke(RemoveTeamFromSeasonCommand $command, AuthContext $authContext)
    {
        IsAdmin::check($authContext->getUser());
        $season = $this->seasonRepository->find($command->getSeasonId());
        $team   = $this->teamRepository->find($command->getTeamId());

        $season->removeTeam($team);
    }
}