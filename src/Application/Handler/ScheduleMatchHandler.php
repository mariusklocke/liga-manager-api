<?php

namespace HexagonalDream\Application\Handler;

use HexagonalDream\Application\Command\ScheduleMatchCommand;
use HexagonalDream\Application\Exception\NotFoundException;
use HexagonalDream\Application\ObjectPersistenceInterface;
use HexagonalDream\Domain\Match;

class ScheduleMatchHandler
{
    /** @var ObjectPersistenceInterface */
    private $persistence;

    public function __construct(ObjectPersistenceInterface $persistence)
    {
        $this->persistence = $persistence;
    }

    /**
     * @param ScheduleMatchCommand $command
     * @throws NotFoundException
     */
    public function handle(ScheduleMatchCommand $command)
    {
        $match = $this->persistence->find(Match::class, $command->getMatchId());
        if (!$match instanceof Match) {
            throw new NotFoundException('Could not find match with ID "' . $command->getMatchId() .  '"');
        }
        $kickoff = $command->getKickoff();
        $this->persistence->transactional(function() use ($match, $kickoff) {
            $match->schedule($kickoff);
        });
    }
}
