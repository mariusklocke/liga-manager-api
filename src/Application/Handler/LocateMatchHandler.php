<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\LocateMatchCommand;
use HexagonalPlayground\Application\OrmRepositoryInterface;
use HexagonalPlayground\Domain\Match;
use HexagonalPlayground\Domain\Pitch;

class LocateMatchHandler
{
    /** @var OrmRepositoryInterface */
    private $matchRepository;

    /** @var OrmRepositoryInterface */
    private $pitchRepository;

    /**
     * @param OrmRepositoryInterface $matchRepository
     * @param OrmRepositoryInterface $pitchRepository
     */
    public function __construct(OrmRepositoryInterface $matchRepository, OrmRepositoryInterface $pitchRepository)
    {
        $this->matchRepository = $matchRepository;
        $this->pitchRepository = $pitchRepository;
    }

    public function __invoke(LocateMatchCommand $command)
    {
        /** @var Match $match */
        $match = $this->matchRepository->find($command->getMatchId());
        /** @var Pitch $pitch */
        $pitch = $this->pitchRepository->find($command->getPitchId());

        $match->locate($pitch);
    }
}
