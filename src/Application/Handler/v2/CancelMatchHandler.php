<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler\v2;

use HexagonalPlayground\Application\Command\v2\CancelMatchCommand;
use HexagonalPlayground\Application\Handler\AuthAwareHandler;
use HexagonalPlayground\Application\Repository\MatchRepositoryInterface;
use HexagonalPlayground\Application\Security\AuthContext;
use HexagonalPlayground\Domain\Event\Event;
use HexagonalPlayground\Domain\MatchEntity;

class CancelMatchHandler implements AuthAwareHandler
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
     * @param CancelMatchCommand $command
     * @param AuthContext $authContext
     * @return array|Event[]
     */
    public function __invoke(CancelMatchCommand $command, AuthContext $authContext): array
    {
        $events = [];

        /** @var MatchEntity $match */
        $match = $this->matchRepository->find($command->getMatchId());

        $authContext->getUser()->assertCanChangeMatch($match);

        $previousResult = $match->getMatchResult();
        $match->cancel($command->getReason());

        $events[] = new Event('match:cancelled', [
            'id' => $match->getId(),
            'reason' => $match->getCancellationReason(),
            'homeScore' => $previousResult !== null ? $previousResult->getHomeScore() : null,
            'guestScore' => $previousResult !== null ? $previousResult->getGuestScore() : null
        ]);

        return $events;
    }
}
