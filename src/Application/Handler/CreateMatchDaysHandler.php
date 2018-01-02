<?php

namespace HexagonalDream\Application\Handler;

use HexagonalDream\Application\Command\CreateMatchDaysCommand;
use HexagonalDream\Application\Exception\NotFoundException;
use HexagonalDream\Application\ObjectPersistenceInterface;
use HexagonalDream\Domain\Match;
use HexagonalDream\Domain\MatchFactory;
use HexagonalDream\Domain\Season;

class CreateMatchDaysHandler
{
    /** @var ObjectPersistenceInterface */
    private $persistence;
    /** @var MatchFactory */
    private $matchFactory;

    public function __construct(ObjectPersistenceInterface $persistence, MatchFactory $matchFactory)
    {
        $this->persistence = $persistence;
        $this->matchFactory = $matchFactory;
    }

    /**
     * @param CreateMatchDaysCommand $command
     * @return Match[]
     */
    public function handle(CreateMatchDaysCommand $command)
    {
        return $this->persistence->transactional(function() use ($command) {
            $season = $this->persistence->find(Season::class, $command->getSeasonId());
            if (!($season instanceof Season)) {
                throw new NotFoundException();
            }

            $matches = $this->matchFactory->createMatchesForSeason($season);
            foreach ($matches as $match) {
                $this->persistence->persist($match);
            }
            return $matches;
        });
    }
}
