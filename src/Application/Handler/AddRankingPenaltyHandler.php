<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\AddRankingPenaltyCommand;
use HexagonalPlayground\Application\Permission\IsAdmin;
use HexagonalPlayground\Application\Repository\SeasonRepositoryInterface;
use HexagonalPlayground\Application\Repository\TeamRepositoryInterface;

class AddRankingPenaltyHandler
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
     * @param AddRankingPenaltyCommand $command
     * @return string
     */
    public function __invoke(AddRankingPenaltyCommand $command)
    {
        IsAdmin::check($command->getAuthenticatedUser());

        $season = $this->seasonRepository->find($command->getSeasonId());
        $team   = $this->teamRepository->find($command->getTeamId());

        $penalty = $season->getRanking()->addPenalty(
            $team,
            $command->getReason(),
            $command->getPoints(),
            $command->getAuthenticatedUser()
        );

        $this->seasonRepository->save($season);

        return $penalty->getId();
    }
}