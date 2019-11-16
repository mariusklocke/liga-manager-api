<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\SubmitMatchResultCommand;
use HexagonalPlayground\Application\Permission\CanChangeMatch;
use HexagonalPlayground\Application\Repository\MatchRepositoryInterface;
use HexagonalPlayground\Domain\Match;
use HexagonalPlayground\Domain\Value\MatchResult;

class SubmitMatchResultHandler
{
    /** @var MatchRepositoryInterface */
    private $matchRepository;

    /**
     * @param MatchRepositoryInterface $matchRepository
     */
    public function __construct(MatchRepositoryInterface $matchRepository)
    {
        $this->matchRepository = $matchRepository;
    }

    /**
     * @param SubmitMatchResultCommand $command
     */
    public function __invoke(SubmitMatchResultCommand $command)
    {
        /** @var Match $match */
        $match = $this->matchRepository->find($command->getMatchId());
        CanChangeMatch::check($command->getAuthenticatedUser(), $match);
        $result = new MatchResult($command->getHomeScore(), $command->getGuestScore());
        $match->submitResult($result, $command->getAuthenticatedUser());
    }
}
