<?php

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\StartSeasonCommand;
use HexagonalPlayground\Application\Exception\InvalidStateException;
use HexagonalPlayground\Application\Exception\NotFoundException;
use HexagonalPlayground\Application\Exception\PersistenceExceptionInterface;
use HexagonalPlayground\Application\ObjectPersistenceInterface;
use HexagonalPlayground\Domain\DomainException;
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
     * @throws PersistenceExceptionInterface
     */
    public function handle(StartSeasonCommand $command)
    {
        /** @var Season $season */
        $season = $this->persistence->find(Season::class, $command->getSeasonId());
        try {
            $season->start($this->collectionFactory);
        } catch (DomainException $e) {
            throw new InvalidStateException($e->getMessage());
        }

        $this->persistence->persist($season);
    }
}