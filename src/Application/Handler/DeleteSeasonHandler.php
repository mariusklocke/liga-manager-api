<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\DeleteSeasonCommand;
use HexagonalPlayground\Application\ObjectPersistenceInterface;
use HexagonalPlayground\Domain\Season;

class DeleteSeasonHandler
{
    /** @var ObjectPersistenceInterface */
    private $persistence;

    public function __construct(ObjectPersistenceInterface $persistence)
    {
        $this->persistence = $persistence;
    }

    public function handle(DeleteSeasonCommand $command)
    {
        /** @var Season $season */
        $season = $this->persistence->find(Season::class, $command->getSeasonId());
        $season->clearMatches()->clearTeams();
        $this->persistence->remove($season);
    }
}
