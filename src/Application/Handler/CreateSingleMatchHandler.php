<?php

namespace HexagonalDream\Application\Handler;

use HexagonalDream\Application\Command\CreateSingleMatchCommand;
use HexagonalDream\Application\Exception\InvalidStateException;
use HexagonalDream\Application\Exception\NotFoundException;
use HexagonalDream\Application\Exception\PersistenceExceptionInterface;
use HexagonalDream\Application\ObjectPersistenceInterface;
use HexagonalDream\Domain\DomainException;
use HexagonalDream\Domain\Match;
use HexagonalDream\Domain\Season;
use HexagonalDream\Domain\Team;
use HexagonalDream\Domain\UuidGeneratorInterface;

class CreateSingleMatchHandler
{
    /** @var ObjectPersistenceInterface */
    private $persistence;

    /** @var UuidGeneratorInterface */
    private $uuidGenerator;

    /**
     * @param ObjectPersistenceInterface $persistence
     * @param UuidGeneratorInterface $uuidGenerator
     */
    public function __construct(ObjectPersistenceInterface $persistence, UuidGeneratorInterface $uuidGenerator)
    {
        $this->persistence = $persistence;
        $this->uuidGenerator = $uuidGenerator;
    }

    /**
     * @param CreateSingleMatchCommand $command
     * @throws NotFoundException
     * @throws PersistenceExceptionInterface
     */
    public function handle(CreateSingleMatchCommand $command)
    {
        /** @var Season $season */
        $season = $this->persistence->find(Season::class, $command->getSeasonId());
        /** @var Team $homeTeam */
        $homeTeam = $this->persistence->find(Team::class, $command->getHomeTeamId());
        /** @var Team $guestTeam */
        $guestTeam = $this->persistence->find(Team::class, $command->getGuestTeamId());

        $match = new Match($this->uuidGenerator, $season, $command->getMatchDay(), $homeTeam, $guestTeam);
        try {
            $season->addMatch($match);
        } catch (DomainException $e) {
            throw new InvalidStateException($e->getMessage());
        }
        $this->persistence->persist($match);
    }
}
