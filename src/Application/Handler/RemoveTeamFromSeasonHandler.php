<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\RemoveTeamFromSeasonCommand;
use HexagonalPlayground\Application\Exception\NotFoundException;
use HexagonalPlayground\Application\OrmRepositoryInterface;
use HexagonalPlayground\Domain\Season;
use HexagonalPlayground\Domain\Team;

class RemoveTeamFromSeasonHandler
{
    /** @var OrmRepositoryInterface */
    private $seasonRepository;

    /** @var OrmRepositoryInterface */
    private $teamRepository;

    /**
     * @param OrmRepositoryInterface $seasonRepository
     * @param OrmRepositoryInterface $teamRepository
     */
    public function __construct(OrmRepositoryInterface $seasonRepository, OrmRepositoryInterface $teamRepository)
    {
        $this->seasonRepository = $seasonRepository;
        $this->teamRepository = $teamRepository;
    }

    /**
     * @param RemoveTeamFromSeasonCommand $command
     * @throws NotFoundException
     */
    public function handle(RemoveTeamFromSeasonCommand $command)
    {
        /** @var Season $season */
        $season = $this->seasonRepository->find($command->getSeasonId());
        /** @var Team $team */
        $team = $this->teamRepository->find($command->getTeamId());

        $season->removeTeam($team);
    }
}