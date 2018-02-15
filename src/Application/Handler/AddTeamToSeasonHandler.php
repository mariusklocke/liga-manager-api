<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\AddTeamToSeasonCommand;
use HexagonalPlayground\Application\Exception\InvalidStateException;
use HexagonalPlayground\Application\Exception\NotFoundException;
use HexagonalPlayground\Application\ObjectPersistenceInterface;
use HexagonalPlayground\Domain\DomainException;
use HexagonalPlayground\Domain\Season;
use HexagonalPlayground\Domain\Team;

class AddTeamToSeasonHandler
{
    /** @var ObjectPersistenceInterface */
    private $persistence;

    /**
     * @param ObjectPersistenceInterface $persistence
     */
    public function __construct(ObjectPersistenceInterface $persistence)
    {
        $this->persistence = $persistence;
    }

    /**
     * @param AddTeamToSeasonCommand $command
     * @throws NotFoundException
     */
    public function handle(AddTeamToSeasonCommand $command)
    {
        /** @var Season $season */
        $season = $this->persistence->find(Season::class, $command->getSeasonId());
        /** @var Team $team */
        $team   = $this->persistence->find(Team::class, $command->getTeamId());

        try {
            $season->addTeam($team);
        } catch (DomainException $e) {
            throw new InvalidStateException($e->getMessage());
        }
    }
}
