<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\AddTeamToSeasonCommand;
use HexagonalPlayground\Domain\Exception\NotFoundException;
use HexagonalPlayground\Application\Repository\SeasonRepositoryInterface;
use HexagonalPlayground\Application\Repository\TeamRepositoryInterface;
use HexagonalPlayground\Application\Security\AuthContext;
use HexagonalPlayground\Domain\Event\Event;
use HexagonalPlayground\Domain\Season;
use HexagonalPlayground\Domain\Team;

class AddTeamToSeasonHandler implements AuthAwareHandler
{
    /** @var SeasonRepositoryInterface */
    private SeasonRepositoryInterface $seasonRepository;

    /** @var TeamRepositoryInterface */
    private TeamRepositoryInterface $teamRepository;

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
     * @param AuthContext $authContext
     * @return array|Event[]
     * @throws NotFoundException
     */
    public function __invoke(AddTeamToSeasonCommand $command, AuthContext $authContext): array
    {
        $authContext->getUser()->assertIsAdmin();

        /** @var Season $season */
        $season = $this->seasonRepository->find($command->getSeasonId());
        /** @var Team $team */
        $team   = $this->teamRepository->find($command->getTeamId());

        $season->addTeam($team);

        return [];
    }
}
