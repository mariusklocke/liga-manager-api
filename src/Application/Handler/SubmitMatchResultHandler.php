<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\SubmitMatchResultCommand;
use HexagonalPlayground\Application\ObjectPersistenceInterface;
use HexagonalPlayground\Domain\Match;
use HexagonalPlayground\Domain\MatchResult;

class SubmitMatchResultHandler
{
    /** @var ObjectPersistenceInterface */
    private $persistence;

    public function __construct(ObjectPersistenceInterface $persistence)
    {
        $this->persistence = $persistence;
    }

    public function handle(SubmitMatchResultCommand $command)
    {
        /** @var Match $match */
        $match  = $this->persistence->find(Match::class, $command->getMatchId());
        $result = new MatchResult($command->getHomeScore(), $command->getGuestScore());
        $match->submitResult($result);
    }
}
