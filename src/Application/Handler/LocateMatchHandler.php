<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\LocateMatchCommand;
use HexagonalPlayground\Application\Repository\MatchRepositoryInterface;
use HexagonalPlayground\Application\Repository\PitchRepositoryInterface;

class LocateMatchHandler
{
    /** @var MatchRepositoryInterface */
    private $matchRepository;

    /** @var PitchRepositoryInterface */
    private $pitchRepository;

    /**
     * @param MatchRepositoryInterface $matchRepository
     * @param PitchRepositoryInterface $pitchRepository
     */
    public function __construct(MatchRepositoryInterface $matchRepository, PitchRepositoryInterface $pitchRepository)
    {
        $this->matchRepository = $matchRepository;
        $this->pitchRepository = $pitchRepository;
    }

    public function __invoke(LocateMatchCommand $command)
    {
        $match = $this->matchRepository->find($command->getMatchId());
        $pitch = $this->pitchRepository->find($command->getPitchId());

        $match->locate($pitch);
    }
}
