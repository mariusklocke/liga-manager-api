<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\CreateSingleMatchCommand;
use HexagonalPlayground\Application\Exception\InvalidStateException;
use HexagonalPlayground\Application\Exception\NotFoundException;
use HexagonalPlayground\Application\ObjectPersistenceInterface;
use HexagonalPlayground\Domain\DomainException;
use HexagonalPlayground\Domain\Match;
use HexagonalPlayground\Domain\Season;
use HexagonalPlayground\Domain\Team;
use HexagonalPlayground\Domain\UuidGeneratorInterface;

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
