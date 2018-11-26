<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\ScheduleMatchCommand;
use HexagonalPlayground\Application\Exception\NotFoundException;
use HexagonalPlayground\Application\Permission\CanChangeMatch;
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
        CanChangeMatch::check($command->getAuthenticatedUser(), $match);
        $match->schedule($command->getKickoff());
    }
}
