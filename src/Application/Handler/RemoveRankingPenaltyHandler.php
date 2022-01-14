<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\RemoveRankingPenaltyCommand;
use HexagonalPlayground\Application\Permission\IsAdmin;
use HexagonalPlayground\Application\Repository\SeasonRepositoryInterface;
use HexagonalPlayground\Application\Security\AuthContext;
use HexagonalPlayground\Domain\Event\Event;
use HexagonalPlayground\Domain\RankingPenalty;
use HexagonalPlayground\Domain\Season;

class RemoveRankingPenaltyHandler implements AuthAwareHandler
{
    /** @var SeasonRepositoryInterface */
    private $seasonRepository;

    /**
     * @param SeasonRepositoryInterface $seasonRepository
     */
    public function __construct(SeasonRepositoryInterface $seasonRepository)
    {
        $this->seasonRepository = $seasonRepository;
    }

    /**
     * @param RemoveRankingPenaltyCommand $command
     * @param AuthContext $authContext
     * @return array|Event[]
     */
    public function __invoke(RemoveRankingPenaltyCommand $command, AuthContext $authContext): array
    {
        $events = [];

        $isAdmin = new IsAdmin($authContext->getUser());
        $isAdmin->check();

        /** @var Season $season */
        $season  = $this->seasonRepository->find($command->getSeasonId());
        $penalty = $season->getRanking()->getPenalty($command->getRankingPenaltyId());
        $season->getRanking()->removePenalty($penalty);

        if ($penalty !== null) {
            $events[] = new Event('ranking:penalty:removed', [
                'seasonId'   => $season->getId(),
                'teamId'     => $penalty->getTeam()->getId(),
                'reason'     => $penalty->getReason(),
                'points'     => $penalty->getPoints(),
                'userId'     => $authContext->getUser()->getId()
            ]);
        }

        return $events;
    }
}
