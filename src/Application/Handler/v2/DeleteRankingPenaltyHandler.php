<?php declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler\v2;

use HexagonalPlayground\Application\Command\v2\DeleteRankingPenaltyCommand;
use HexagonalPlayground\Application\Handler\AuthAwareHandler;
use HexagonalPlayground\Application\Permission\IsAdmin;
use HexagonalPlayground\Application\Repository\RankingPenaltyRepositoryInterface;
use HexagonalPlayground\Application\Security\AuthContext;
use HexagonalPlayground\Domain\Event\Event;
use HexagonalPlayground\Domain\RankingPenalty;

class DeleteRankingPenaltyHandler implements AuthAwareHandler
{
    private RankingPenaltyRepositoryInterface $rankingPenaltyRepository;

    public function __construct(RankingPenaltyRepositoryInterface $rankingPenaltyRepository)
    {
        $this->rankingPenaltyRepository = $rankingPenaltyRepository;
    }

    /**
     * @param DeleteRankingPenaltyCommand $command
     * @param AuthContext $authContext
     * @return array|Event[]
     */
    public function __invoke(DeleteRankingPenaltyCommand $command, AuthContext $authContext): array
    {
        $isAdmin = new IsAdmin($authContext->getUser());
        $isAdmin->check();

        /** @var RankingPenalty $penalty */
        $penalty = $this->rankingPenaltyRepository->find($command->getId());
        $ranking = $penalty->getRanking();
        $ranking->removePenalty($penalty);

        $events = [];
        $events[] = new Event('ranking:penalty:removed', [
            'seasonId'   => $ranking->getSeason()->getId(),
            'teamId'     => $penalty->getTeam()->getId(),
            'reason'     => $penalty->getReason(),
            'points'     => $penalty->getPoints(),
            'userId'     => $authContext->getUser()->getId()
        ]);

        return $events;
    }
}
