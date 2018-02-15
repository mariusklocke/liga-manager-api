<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\CancelMatchCommand;
use HexagonalPlayground\Application\Exception\InvalidStateException;
use HexagonalPlayground\Application\Exception\NotFoundException;
use HexagonalPlayground\Application\ObjectPersistenceInterface;
use HexagonalPlayground\Domain\DomainException;
use HexagonalPlayground\Domain\Match;

class CancelMatchHandler
{
    /** @var ObjectPersistenceInterface */
    private $persistence;

    /**
     * @param ObjectPersistenceInterface $persistence
     */
    public function __construct(ObjectPersistenceInterface $persistence)
    {
        $this->persistence = $persistence;
    }

    /**
     * @param CancelMatchCommand $command
     * @throws NotFoundException
     */
    public function handle(CancelMatchCommand $command)
    {
        /** @var Match $match */
        $match = $this->persistence->find(Match::class, $command->getMatchId());
        try {
            $match->cancel();
        } catch (DomainException $e) {
            throw new InvalidStateException($e->getMessage());
        }
    }
}
