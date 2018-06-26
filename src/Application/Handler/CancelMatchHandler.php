<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\CancelMatchCommand;
use HexagonalPlayground\Application\Exception\NotFoundException;
use HexagonalPlayground\Application\Repository\MatchRepositoryInterface;

class CancelMatchHandler
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
     * @throws NotFoundException
     */
    public function __invoke(CancelMatchCommand $command)
    {
        $match = $this->matchRepository->find($command->getMatchId());
        $match->cancel();
    }
}
