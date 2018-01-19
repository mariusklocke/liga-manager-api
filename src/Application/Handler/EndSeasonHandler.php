<?php

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\EndSeasonCommand;
use HexagonalPlayground\Application\ObjectPersistenceInterface;
use HexagonalPlayground\Domain\Season;

class EndSeasonHandler
{
    /** @var ObjectPersistenceInterface */
    private $persistence;

    public function __construct(ObjectPersistenceInterface $persistence)
    {
        $this->persistence = $persistence;
    }

    public function handle(EndSeasonCommand $command)
    {
        /** @var Season $season */
        $season = $this->persistence->find(Season::class, $command->getSeasonId());
        $season->end();
    }
}
