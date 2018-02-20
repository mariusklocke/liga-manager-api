<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\CreateSeasonCommand;
use HexagonalPlayground\Application\Factory\SeasonFactory;
use HexagonalPlayground\Application\ObjectPersistenceInterface;

class CreateSeasonHandler
{
    /** @var ObjectPersistenceInterface */
    private $persistence;

    /** @var SeasonFactory */
    private $seasonFactory;

    public function __construct(ObjectPersistenceInterface $persistence, SeasonFactory $seasonFactory)
    {
        $this->persistence = $persistence;
        $this->seasonFactory = $seasonFactory;
    }

    /**
     * @param CreateSeasonCommand $command
     * @return string
     */
    public function handle(CreateSeasonCommand $command)
    {
        $season = $this->seasonFactory->createSeason($command->getName());
        $this->persistence->persist($season);
        return $season->getId();
    }
}