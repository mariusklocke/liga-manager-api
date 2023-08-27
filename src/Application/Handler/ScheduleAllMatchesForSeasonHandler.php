<?php declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\ScheduleAllMatchesForSeasonCommand;
use HexagonalPlayground\Application\Repository\PitchRepositoryInterface;
use HexagonalPlayground\Application\Repository\SeasonRepositoryInterface;
use HexagonalPlayground\Application\Security\AuthContext;
use HexagonalPlayground\Domain\Event\Event;
use HexagonalPlayground\Domain\MatchScheduler;
use HexagonalPlayground\Domain\Pitch;
use HexagonalPlayground\Domain\Season;

class ScheduleAllMatchesForSeasonHandler implements AuthAwareHandler
{
    /** @var SeasonRepositoryInterface */
    private SeasonRepositoryInterface $seasonRepository;

    /** @var MatchScheduler */
    private MatchScheduler $matchScheduler;

    /** @var PitchRepositoryInterface */
    private PitchRepositoryInterface $pitchRepository;

    /**
     * @param SeasonRepositoryInterface $seasonRepository
     * @param MatchScheduler $matchScheduler
     * @param PitchRepositoryInterface $pitchRepository
     */
    public function __construct(
        SeasonRepositoryInterface $seasonRepository,
        MatchScheduler $matchScheduler,
        PitchRepositoryInterface $pitchRepository
    ) {
        $this->seasonRepository = $seasonRepository;
        $this->matchScheduler = $matchScheduler;
        $this->pitchRepository = $pitchRepository;
    }

    /**
     * @param ScheduleAllMatchesForSeasonCommand $command
     * @param AuthContext $authContext
     * @return array|Event[]
     */
    public function __invoke(ScheduleAllMatchesForSeasonCommand $command, AuthContext $authContext): array
    {
        $authContext->getUser()->assertIsAdmin();

        /** @var Season $season */
        $season = $this->seasonRepository->find($command->getSeasonId());

        /** @var Pitch[] $pitches */
        $pitches = [];

        foreach ($command->getMatchAppointments() as $appointment) {
            if (!isset($pitches[$appointment->getPitchId()])) {
                $pitches[$appointment->getPitchId()] = $this->pitchRepository->find($appointment->getPitchId());
            }
        }

        foreach ($season->getMatchDays() as $matchDay) {
            $this->matchScheduler->scheduleMatchesForMatchDay($matchDay, $command->getMatchAppointments(), $pitches);
        }

        return [];
    }
}
