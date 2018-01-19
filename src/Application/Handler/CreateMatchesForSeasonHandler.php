<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\CreateMatchesForSeasonCommand;
use HexagonalPlayground\Application\Exception\InvalidStateException;
use HexagonalPlayground\Application\Exception\NotFoundException;
use HexagonalPlayground\Application\Exception\PersistenceExceptionInterface;
use HexagonalPlayground\Application\ObjectPersistenceInterface;
use HexagonalPlayground\Application\Factory\MatchFactory;
use HexagonalPlayground\Domain\Season;

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
