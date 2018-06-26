<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\ScheduleMatchCommand;
use HexagonalPlayground\Application\Exception\NotFoundException;
use HexagonalPlayground\Application\Repository\MatchRepositoryInterface;

class ScheduleMatchHandler
{
    /** @var MatchRepositoryInterface */
    private $matchRepository;

    /**
     * @param MatchRepositoryInterface $matchRepository
     */
    public function __construct(MatchRepositoryInterface $matchRepository)
    {
        $this->matchRepository = $matchRepository;
    }

    /**
     * @param ScheduleMatchCommand $command
     * @throws NotFoundException
     */
    public function __invoke(ScheduleMatchCommand $command)
    {
        $match = $this->matchRepository->find($command->getMatchId());
        $match->schedule($command->getKickoff());
    }
}
