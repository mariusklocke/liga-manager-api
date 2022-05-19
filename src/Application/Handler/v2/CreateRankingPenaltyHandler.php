<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler\v2;

use HexagonalPlayground\Application\Command\v2\CreateRankingPenaltyCommand;
use HexagonalPlayground\Application\Handler\AuthAwareHandler;
use HexagonalPlayground\Application\Permission\IsAdmin;
use HexagonalPlayground\Application\Repository\SeasonRepositoryInterface;
use HexagonalPlayground\Application\Repository\TeamRepositoryInterface;
use HexagonalPlayground\Application\Security\AuthContext;
use HexagonalPlayground\Domain\Event\Event;
use HexagonalPlayground\Domain\RankingPenalty;
use HexagonalPlayground\Domain\Season;
use HexagonalPlayground\Domain\Team;

class CreateRankingPenaltyHandler implements AuthAwareHandler
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
     * @param CreateRankingPenaltyCommand $command
     * @param AuthContext $authContext
     * @return array|Event[]
     */
    public function __invoke(CreateRankingPenaltyCommand $command, AuthContext $authContext): array
    {
        $events = [];

        $isAdmin = new IsAdmin($authContext->getUser());
        $isAdmin->check();

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