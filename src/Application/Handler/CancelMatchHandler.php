<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\CancelMatchCommand;
use HexagonalPlayground\Application\Exception\NotFoundException;
use HexagonalPlayground\Application\Permission\CanChangeMatch;
use HexagonalPlayground\Application\Repository\MatchRepositoryInterface;
use HexagonalPlayground\Application\Security\AuthContext;
use HexagonalPlayground\Domain\Match;

class CancelMatchHandler implements AuthAwareHandler
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
     * @param CancelMatchCommand $command
     * @param AuthContext $authContext
     * @throws NotFoundException
     */
    public function __invoke(CancelMatchCommand $command, AuthContext $authContext)
    {
        /** @var Match $match */
        $match = $this->matchRepository->find($command->getMatchId());
        $canChangeMatch = new CanChangeMatch($authContext->getUser(), $match);

        $canChangeMatch->check();
        $match->cancel($command->getReason());
    }
}
