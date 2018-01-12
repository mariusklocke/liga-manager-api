<?php

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\SubmitMatchResultCommand;
use HexagonalPlayground\Application\Exception\InvalidStateException;
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
        $match = $this->persistence->find(Match::class, $command->getMatchId());
        if ($match->hasResult()) {
            throw new InvalidStateException('Match result has already been submitted');
        }
        $season = $match->getSeason();
        if (!$season->hasStarted()) {
            throw new InvalidStateException('Season has not been started');
        }

        $result = new MatchResult($command->getHomeScore(), $command->getGuestScore());
        $match->submitResult($result);
        $season->getRanking()->addResult($match->getHomeTeam()->getId(), $match->getGuestTeam()->getId(), $result);
    }
}
