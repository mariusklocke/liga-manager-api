<?php
/**
 * StartSeasonHandler.php
 *
 * @author    Marius Klocke <marius.klocke@eventim.de>
 * @copyright Copyright (c) 2017, CTS EVENTIM Solutions GmbH
 */

namespace HexagonalDream\Application\Handler;

use HexagonalDream\Application\Command\StartSeasonCommand;
use HexagonalDream\Application\Exception\NotFoundException;
use HexagonalDream\Application\Exception\PersistenceExceptionInterface;
use HexagonalDream\Application\ObjectPersistenceInterface;
use HexagonalDream\Domain\DomainException;
use HexagonalDream\Domain\Season;

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
     * @throws DomainException
     */
    public function handle(StartSeasonCommand $command)
    {
        /** @var Season $season */
        $season = $this->persistence->find(Season::class, $command->getSeasonId());
        $season->start($this->collectionFactory);
        $this->persistence->persist($season);
    }
}