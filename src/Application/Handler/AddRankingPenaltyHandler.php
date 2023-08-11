<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\AddRankingPenaltyCommand;
use HexagonalPlayground\Application\Repository\SeasonRepositoryInterface;
use HexagonalPlayground\Application\Repository\TeamRepositoryInterface;
use HexagonalPlayground\Application\Security\AuthContext;
use HexagonalPlayground\Domain\Event\Event;
use HexagonalPlayground\Domain\RankingPenalty;
use HexagonalPlayground\Domain\Season;
use HexagonalPlayground\Domain\Team;

class AddRankingPenaltyHandler implements AuthAwareHandler
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
     * @param AddRankingPenaltyCommand $command
     * @param AuthContext $authContext
     * @return array|Event[]
     */
    public function __invoke(AddRankingPenaltyCommand $command, AuthContext $authContext): array
    {
        $authContext->getUser()->assertIsAdmin();

        /** @var Season $season */
        $season  = $this->seasonRepository->find($command->getSeasonId());
        /** @var Team $team */
        $team    = $this->teamRepository->find($command->getTeamId());
        $ranking = $season->getRanking();

        $penalty = new RankingPenalty(
            $command->getId(),
            $season->getRanking(),
            $team,
            $command->getReason(),
            $command->getPoints()
        );

        $ranking->addPenalty($penalty);

        $this->seasonRepository->save($season);

        $events = [];
        $events[] = new Event('ranking:penalty:added', [
            'seasonId'   => $season->getId(),
            'teamId'     => $team->getId(),
            'reason'     => $command->getReason(),
            'points'     => $command->getPoints(),
            'userId'     => $authContext->getUser()->getId()
        ]);

        return $events;
    }
}
