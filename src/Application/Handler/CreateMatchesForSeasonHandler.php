<?php

namespace HexagonalDream\Application\Handler;

use HexagonalDream\Application\Command\CreateMatchesForSeasonCommand;
use HexagonalDream\Application\Exception\InvalidStateException;
use HexagonalDream\Application\Exception\NotFoundException;
use HexagonalDream\Application\Exception\PersistenceExceptionInterface;
use HexagonalDream\Application\ObjectPersistenceInterface;
use HexagonalDream\Domain\MatchFactory;
use HexagonalDream\Domain\Season;

class CreateMatchesForSeasonHandler
{
    /** @var ObjectPersistenceInterface */
    private $persistence;
    /** @var MatchFactory */
    private $matchFactory;

    /**
     * @param ObjectPersistenceInterface $persistence
     * @param MatchFactory $matchFactory
     */
    public function __construct(ObjectPersistenceInterface $persistence, MatchFactory $matchFactory)
    {
        $this->persistence = $persistence;
        $this->matchFactory = $matchFactory;
    }

    /**
     * @param CreateMatchesForSeasonCommand $command
     * @throws PersistenceExceptionInterface
     * @throws NotFoundException
     */
    public function handle(CreateMatchesForSeasonCommand $command)
    {
        /** @var Season $season */
        $season = $this->persistence->find(Season::class, $command->getSeasonId());
        if ($season->hasStarted()) {
            throw new InvalidStateException('Cannot add matches to season which has already started');
        }
        $season->clearMatches();
        $matches = $this->matchFactory->createMatchesForSeason($season);
        foreach ($matches as $match) {
            $season->addMatch($match);
            $this->persistence->persist($match);
        }
    }
}
