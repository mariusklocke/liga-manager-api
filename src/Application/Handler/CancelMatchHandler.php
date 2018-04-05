<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\CancelMatchCommand;
use HexagonalPlayground\Application\Exception\NotFoundException;
use HexagonalPlayground\Application\OrmRepositoryInterface;
use HexagonalPlayground\Domain\Match;

class CancelMatchHandler
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
     * @param CancelMatchCommand $command
     * @throws NotFoundException
     */
    public function handle(CancelMatchCommand $command)
    {
        /** @var Match $match */
        $match = $this->matchRepository->find($command->getMatchId());
        $match->cancel();
    }
}
