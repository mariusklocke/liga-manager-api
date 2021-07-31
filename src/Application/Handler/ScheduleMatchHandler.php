<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\ScheduleMatchCommand;
use HexagonalPlayground\Application\Exception\NotFoundException;
use HexagonalPlayground\Application\Permission\CanChangeMatch;
use HexagonalPlayground\Application\Repository\MatchRepositoryInterface;
use HexagonalPlayground\Application\Security\AuthContext;
use HexagonalPlayground\Domain\MatchEntity;

class ScheduleMatchHandler implements AuthAwareHandler
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
     * @param AuthContext $authContext
     * @throws NotFoundException
     */
    public function __invoke(ScheduleMatchCommand $command, AuthContext $authContext): void
    {
        /** @var MatchEntity $match */
        $match = $this->matchRepository->find($command->getMatchId());
        $canChangeMatch = new CanChangeMatch($authContext->getUser(), $match);
        $canChangeMatch->check();
        $match->schedule($command->getKickoff());
    }
}
