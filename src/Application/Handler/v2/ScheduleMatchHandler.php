<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler\v2;

use HexagonalPlayground\Application\Command\v2\ScheduleMatchCommand;
use HexagonalPlayground\Application\Handler\AuthAwareHandler;
use HexagonalPlayground\Application\Repository\MatchRepositoryInterface;
use HexagonalPlayground\Application\Security\AuthContext;
use HexagonalPlayground\Domain\Event\Event;
use HexagonalPlayground\Domain\MatchEntity;

class ScheduleMatchHandler implements AuthAwareHandler
{
    /** @var MatchRepositoryInterface */
    private MatchRepositoryInterface $matchRepository;

    /**
     * @param MatchRepositoryInterface $matchRepository
     */
    public function __construct(MatchRepositoryInterface $matchRepository)
    {
        $this->matchRepository = $matchRepository;
    }

    /**
     * @param ScheduleMatchCommand $command
     * @param AuthContext $authContext
     * @return array|Event[]
     */
    public function __invoke(ScheduleMatchCommand $command, AuthContext $authContext): array
    {
        $events = [];

        /** @var MatchEntity $match */
        $match = $this->matchRepository->find($command->getMatchId());
        $authContext->getUser()->assertCanChangeMatch($match);
        $match->schedule($command->getKickoff());

        $events[] = new Event('match:scheduled', [
            'matchId' => $match->getId(),
            'kickoff' => $match->getKickoff()->getTimestamp()
        ]);

        return $events;
    }
}
