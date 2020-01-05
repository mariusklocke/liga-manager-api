<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\LocateMatchCommand;
use HexagonalPlayground\Application\Permission\CanChangeMatch;
use HexagonalPlayground\Application\Repository\MatchRepositoryInterface;
use HexagonalPlayground\Application\Repository\PitchRepositoryInterface;
use HexagonalPlayground\Application\Security\AuthContext;

class LocateMatchHandler implements AuthAwareHandler
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

    /**
     * @param LocateMatchCommand $command
     * @param AuthContext $authContext
     */
    public function __invoke(LocateMatchCommand $command, AuthContext $authContext)
    {
        $match = $this->matchRepository->find($command->getMatchId());
        $pitch = $this->pitchRepository->find($command->getPitchId());
        CanChangeMatch::check($authContext->getUser(), $match);
        $match->locate($pitch);
    }
}
