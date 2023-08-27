<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\LocateMatchCommand;
use HexagonalPlayground\Application\Repository\MatchRepositoryInterface;
use HexagonalPlayground\Application\Repository\PitchRepositoryInterface;
use HexagonalPlayground\Application\Security\AuthContext;
use HexagonalPlayground\Domain\Event\Event;
use HexagonalPlayground\Domain\MatchEntity;
use HexagonalPlayground\Domain\Pitch;

class LocateMatchHandler implements AuthAwareHandler
{
    /** @var MatchRepositoryInterface */
    private MatchRepositoryInterface $matchRepository;

    /** @var PitchRepositoryInterface */
    private PitchRepositoryInterface $pitchRepository;

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
     * @return array|Event[]
     */
    public function __invoke(LocateMatchCommand $command, AuthContext $authContext): array
    {
        $events = [];

        /** @var MatchEntity $match */
        $match = $this->matchRepository->find($command->getMatchId());
        /** @var Pitch $pitch */
        $pitch = $this->pitchRepository->find($command->getPitchId());

        $authContext->getUser()->assertCanChangeMatch($match);

        $match->locate($pitch);

        $events[] = new Event('match:located', [
            'matchId' => $match->getId(),
            'pitchId' => $pitch->getId()
        ]);

        return $events;
    }
}
