<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\StartSeasonCommand;
use HexagonalPlayground\Application\Exception\NotFoundException;
use HexagonalPlayground\Application\ObjectPersistenceInterface;
use HexagonalPlayground\Domain\Season;

class StartSeasonHandler
{
    /** @var ObjectPersistenceInterface */
    private $persistence;

    /** @var callable */
    private $collectionFactory;

    /**
     * @param ObjectPersistenceInterface $persistence
     * @param callable $collectionFactory
     */
    public function __construct(ObjectPersistenceInterface $persistence, callable $collectionFactory)
    {
        $this->persistence = $persistence;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @param StartSeasonCommand $command
     * @throws NotFoundException
     */
    public function handle(StartSeasonCommand $command)
    {
        /** @var Season $season */
        $season = $this->persistence->find(Season::class, $command->getSeasonId());
        $season->start($this->collectionFactory);
        $this->persistence->persist($season);
    }
}