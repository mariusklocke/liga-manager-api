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
use HexagonalDream\Application\ObjectPersistenceInterface;
use HexagonalDream\Domain\Season;

class StartSeasonHandler
{
    /** @var ObjectPersistenceInterface */
    private $persistence;

    /** @var callable */
    private $collectionFactory;

    public function __construct(ObjectPersistenceInterface $persistence, callable $collectionFactory)
    {
        $this->persistence = $persistence;
        $this->collectionFactory = $collectionFactory;
    }

    public function handle(StartSeasonCommand $command)
    {
        $this->persistence->transactional(function() use ($command) {
            $season = $this->persistence->find(Season::class, $command->getSeasonId());
            if (!$season instanceof Season) {
                throw new NotFoundException();
            }

            $season->start($this->collectionFactory);
            $this->persistence->persist($season);
        });
    }
}