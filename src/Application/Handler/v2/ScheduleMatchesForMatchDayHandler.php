<?php declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler\v2;

use HexagonalPlayground\Application\Command\v2\ScheduleMatchesForMatchDayCommand;
use HexagonalPlayground\Application\Handler\AuthAwareHandler;
use HexagonalPlayground\Application\Permission\IsAdmin;
use HexagonalPlayground\Application\Repository\MatchDayRepositoryInterface;
use HexagonalPlayground\Application\Repository\PitchRepositoryInterface;
use HexagonalPlayground\Application\Security\AuthContext;
use HexagonalPlayground\Domain\Event\Event;
use HexagonalPlayground\Domain\MatchDay;
use HexagonalPlayground\Domain\MatchScheduler;
use HexagonalPlayground\Domain\Pitch;

class ScheduleMatchesForMatchDayHandler implements AuthAwareHandler
{
    /** @var MatchDayRepositoryInterface */
    private MatchDayRepositoryInterface $matchDayRepository;

    /** @var MatchScheduler */
    private MatchScheduler $matchScheduler;

    /** @var PitchRepositoryInterface */
    private PitchRepositoryInterface $pitchRepository;

    /**
     * @param MatchDayRepositoryInterface $matchDayRepository
     * @param MatchScheduler $matchScheduler
     * @param PitchRepositoryInterface $pitchRepository
     */
    public function __construct(
        MatchDayRepositoryInterface $matchDayRepository,
        MatchScheduler $matchScheduler,
        PitchRepositoryInterface $pitchRepository
    ) {
        $this->matchDayRepository = $matchDayRepository;
        $this->matchScheduler = $matchScheduler;
        $this->pitchRepository = $pitchRepository;
    }

    /**
     * @param ScheduleMatchesForMatchDayCommand $command
     * @param AuthContext $authContext
     * @return array|Event[]
     */
    public function __invoke(ScheduleMatchesForMatchDayCommand $command, AuthContext $authContext): array
    {
        $isAdmin = new IsAdmin($authContext->getUser());
        $isAdmin->check();

        /** @var MatchDay $matchDay */
        $matchDay = $this->matchDayRepository->find($command->getMatchDayId());

        /** @var Pitch[] $pitches */
        $pitches = [];

        foreach ($command->getMatchAppointments() as $appointment) {
            if (!isset($pitches[$appointment->getPitchId()])) {
                $pitches[$appointment->getPitchId()] = $this->pitchRepository->find($appointment->getPitchId());
            }
        }

        $this->matchScheduler->scheduleMatchesForMatchDay($matchDay, $command->getMatchAppointments(), $pitches);

        return [];
    }
}
