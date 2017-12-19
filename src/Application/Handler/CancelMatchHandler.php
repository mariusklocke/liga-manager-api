<?php

namespace HexagonalDream\Application\Handler;

use HexagonalDream\Application\Command\CancelMatchCommand;
use HexagonalDream\Application\Exception\NotFoundException;
use HexagonalDream\Application\ObjectPersistenceInterface;
use HexagonalDream\Domain\Match;

class CancelMatchHandler
{
    /** @var ObjectPersistenceInterface */
    private $persistence;

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
        $match = $this->persistence->find(Match::class, $command->getMatchId());
        if (!$match instanceof Match) {
            throw new NotFoundException('Could not find match with ID "' . $command->getMatchId() .  '"');
        }
        $this->persistence->transactional(function() use ($match) {
            $match->cancel();
        });
    }
}
