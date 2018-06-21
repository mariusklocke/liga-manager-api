<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\ScheduleMatchCommand;
use HexagonalPlayground\Application\Exception\NotFoundException;
use HexagonalPlayground\Application\OrmRepositoryInterface;
use HexagonalPlayground\Domain\Match;

class ScheduleMatchHandler
{
    /** @var OrmRepositoryInterface */
    private $matchRepository;

    /**
     * @param OrmRepositoryInterface $matchRepository
     */
    public function __construct(OrmRepositoryInterface $matchRepository)
    {
        $this->matchRepository = $matchRepository;
    }

    /**
     * @param ScheduleMatchCommand $command
     * @throws NotFoundException
     */
    public function __invoke(ScheduleMatchCommand $command)
    {
        /** @var Match $match */
        $match = $this->matchRepository->find($command->getMatchId());
        $match->schedule($command->getKickoff());
    }
}
