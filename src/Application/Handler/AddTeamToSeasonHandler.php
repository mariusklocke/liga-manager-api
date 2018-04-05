<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\AddTeamToSeasonCommand;
use HexagonalPlayground\Application\Exception\NotFoundException;
use HexagonalPlayground\Application\OrmRepositoryInterface;
use HexagonalPlayground\Domain\Season;
use HexagonalPlayground\Domain\Team;

class AddTeamToSeasonHandler
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
     * @param AddTeamToSeasonCommand $command
     * @throws NotFoundException
     */
    public function handle(AddTeamToSeasonCommand $command)
    {
        /** @var Season $season */
        $season = $this->seasonRepository->find($command->getSeasonId());
        /** @var Team $team */
        $team   = $this->teamRepository->find($command->getTeamId());

        $season->addTeam($team);
    }
}
