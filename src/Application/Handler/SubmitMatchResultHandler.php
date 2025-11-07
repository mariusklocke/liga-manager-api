<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\SubmitMatchResultCommand;
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

        $authContext->getUser()->assertCanChangeMatch($match);

        if ($command->getHomeScore() !== null && $command->getGuestScore() !== null) {
            $match->submitResult(new MatchResult($command->getHomeScore(), $command->getGuestScore()));
        } else {
            $match->clearResult();
        }

        $events[] = new Event('match:result:submitted', [
            'matchId' => $match->getId(),
            'homeScore' => $match->getMatchResult() ? $match->getMatchResult()->getHomeScore() : null,
            'guestScore' => $match->getMatchResult() ? $match->getMatchResult()->getGuestScore() : null,
            'userId' => $authContext->getUser()->getId()
        ]);

        return $events;
    }
}
