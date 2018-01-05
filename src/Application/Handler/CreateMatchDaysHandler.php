<?php

namespace HexagonalDream\Application\Handler;

use HexagonalDream\Application\Command\CreateMatchDaysCommand;
use HexagonalDream\Application\Exception\NotFoundException;
use HexagonalDream\Application\Exception\PersistenceExceptionInterface;
use HexagonalDream\Application\ObjectPersistenceInterface;
use HexagonalDream\Domain\DomainException;
use HexagonalDream\Domain\Match;
use HexagonalDream\Domain\MatchFactory;
use HexagonalDream\Domain\Season;

class CreateMatchDaysHandler
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
     * @param CreateMatchDaysCommand $command
     * @return Match[]
     * @throws PersistenceExceptionInterface
     * @throws NotFoundException
     * @throws DomainException
     */
    public function handle(CreateMatchDaysCommand $command)
    {
        /** @var Season $season */
        $season = $this->persistence->find(Season::class, $command->getSeasonId());
        $matches = $this->matchFactory->createMatchesForSeason($season);
        foreach ($matches as $match) {
            $this->persistence->persist($match);
        }
        return $matches;
    }
}
