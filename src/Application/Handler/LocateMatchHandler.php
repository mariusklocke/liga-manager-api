<?php

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\LocateMatchCommand;
use HexagonalPlayground\Application\ObjectPersistenceInterface;
use HexagonalPlayground\Domain\Match;
use HexagonalPlayground\Domain\Pitch;

class LocateMatchHandler
{
    /** @var ObjectPersistenceInterface */
    private $persistence;

    public function __construct(ObjectPersistenceInterface $persistence)
    {
        $this->persistence = $persistence;
    }

    public function handle(LocateMatchCommand $command)
    {
        /** @var Match $match */
        $match = $this->persistence->find(Match::class, $command->getMatchId());
        /** @var Pitch $pitch */
        $pitch = $this->persistence->find(Pitch::class, $command->getPitchId());

        $match->locate($pitch);
    }
}
