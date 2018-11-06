<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\AddTeamToSeasonCommand;
use HexagonalPlayground\Application\Exception\NotFoundException;
use HexagonalPlayground\Application\Permission\IsAdmin;
use HexagonalPlayground\Application\Repository\SeasonRepositoryInterface;
use HexagonalPlayground\Application\Repository\TeamRepositoryInterface;

class AddTeamToSeasonHandler
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
     * @param AddTeamToSeasonCommand $command
     * @throws NotFoundException
     */
    public function __invoke(AddTeamToSeasonCommand $command)
    {
        IsAdmin::check($command->getAuthenticatedUser());
        $season = $this->seasonRepository->find($command->getSeasonId());
        $team   = $this->teamRepository->find($command->getTeamId());

        $season->addTeam($team);
    }
}
