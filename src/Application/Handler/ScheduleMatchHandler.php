<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\ScheduleMatchCommand;
use HexagonalPlayground\Application\Repository\MatchDayRepositoryInterface;
use HexagonalPlayground\Domain\Exception\NotFoundException;
use HexagonalPlayground\Application\Repository\MatchRepositoryInterface;
use HexagonalPlayground\Application\Security\AuthContext;
use HexagonalPlayground\Domain\Event\Event;
use HexagonalPlayground\Domain\MatchDay;
use HexagonalPlayground\Domain\MatchEntity;

class ScheduleMatchHandler implements AuthAwareHandler
{
    private MatchRepositoryInterface $matchRepository;
    private MatchDayRepositoryInterface $matchDayRepository;

    /**
     * @param MatchRepositoryInterface $matchRepository
     * @param MatchDayRepositoryInterface $matchDayRepository
     */
    public function __construct(MatchRepositoryInterface $matchRepository, MatchDayRepositoryInterface $matchDayRepository)
    {
        $this->matchRepository = $matchRepository;
        $this->matchDayRepository = $matchDayRepository;
    }

    /**
     * @param ScheduleMatchCommand $command
     * @param AuthContext $authContext
     * @return array|Event[]
     * @throws NotFoundException
     */
    public function __invoke(ScheduleMatchCommand $command, AuthContext $authContext): array
    {
        /** @var MatchEntity $match */
        $match = $this->matchRepository->find($command->getMatchId());

        $authContext->getUser()->assertCanChangeMatch($match);

        $eventPayload = ['matchId' => $match->getId()];

        if ($command->getMatchDayId() !== null) {
            /** @var MatchDay $matchDay */
            $matchDay = $this->matchDayRepository->find($command->getMatchDayId());
            $match->setMatchDay($matchDay);
            $eventPayload['matchDayId'] = $match->getMatchDay()->getId();
        }

        if ($command->getKickoff() !== null) {
            $match->setKickoff($command->getKickoff());
            $eventPayload['kickoff'] = $match->getKickoff()->getTimestamp();
        }

        $event = new Event('match:scheduled', $eventPayload);

        return [$event];
    }
}
