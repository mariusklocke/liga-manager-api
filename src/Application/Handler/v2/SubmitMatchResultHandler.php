<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler\v2;

use HexagonalPlayground\Application\Command\v2\SubmitMatchResultCommand;
use HexagonalPlayground\Application\Handler\AuthAwareHandler;
use HexagonalPlayground\Application\Permission\CanChangeMatch;
use HexagonalPlayground\Application\Repository\MatchRepositoryInterface;
use HexagonalPlayground\Application\Security\AuthContext;
use HexagonalPlayground\Domain\Event\Event;
use HexagonalPlayground\Domain\MatchEntity;
use HexagonalPlayground\Domain\Value\MatchResult;

class SubmitMatchResultHandler implements AuthAwareHandler
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
     * @param SubmitMatchResultCommand $command
     * @param AuthContext $authContext
     * @return array|Event[]
     */
    public function __invoke(SubmitMatchResultCommand $command, AuthContext $authContext): array
    {
        $events = [];

        /** @var MatchEntity $match */
        $match = $this->matchRepository->find($command->getMatchId());
        $canChangeMatch = new CanChangeMatch($authContext->getUser(), $match);
        $canChangeMatch->check();
        $match->submitResult($command->getMatchResult());

        $events[] = new Event('match:result:submitted', [
            'matchId' => $match->getId(),
            'homeScore' => $match->getMatchResult()->getHomeScore(),
            'guestScore' => $match->getMatchResult()->getGuestScore(),
            'userId' => $authContext->getUser()->getId()
        ]);

        return $events;
    }
}