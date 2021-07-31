<?php declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use Closure;
use DateTimeImmutable;
use HexagonalPlayground\Application\Command\ScheduleAllMatchesForSeasonCommand;
use HexagonalPlayground\Application\Permission\IsAdmin;
use HexagonalPlayground\Application\Repository\PitchRepositoryInterface;
use HexagonalPlayground\Application\Repository\SeasonRepositoryInterface;
use HexagonalPlayground\Application\Security\AuthContext;
use HexagonalPlayground\Domain\Season;
use HexagonalPlayground\Domain\Value\MatchAppointment;
use HexagonalPlayground\Domain\DomainException;
use HexagonalPlayground\Domain\MatchEntity;
use HexagonalPlayground\Domain\MatchDay;
use HexagonalPlayground\Domain\Util\Assert;

class ScheduleAllMatchesForSeasonHandler implements AuthAwareHandler
{
    /** @var SeasonRepositoryInterface */
    private $seasonRepository;

    /** @var PitchRepositoryInterface */
    private $pitchRepository;

    /**
     * @param SeasonRepositoryInterface $seasonRepository
     * @param PitchRepositoryInterface $pitchRepository
     */
    public function __construct(SeasonRepositoryInterface $seasonRepository, PitchRepositoryInterface $pitchRepository)
    {
        $this->seasonRepository = $seasonRepository;
        $this->pitchRepository  = $pitchRepository;
    }

    /**
     * @param ScheduleAllMatchesForSeasonCommand $command
     * @param AuthContext $authContext
     */
    public function __invoke(ScheduleAllMatchesForSeasonCommand $command, AuthContext $authContext): void
    {
        $isAdmin = new IsAdmin($authContext->getUser());
        $isAdmin->check();

        /** @var Season $season */
        $season = $this->seasonRepository->find($command->getSeasonId());
        $pitches = [];

        foreach ($season->getMatchDays() as $matchDay) {
            $appointments = $command->getMatchAppointments();
            $matches = $matchDay->getMatches();
            Assert::true(
                count($appointments) >= count($matches),
                'Cannot schedule MatchDay with less appointments than matches.'
            );

            shuffle($appointments);
            usort($matches, $this->getMatchSortingClosure($appointments));

            foreach ($matches as $match) {
                $appointment = $this->findAppointment($match, $appointments);

                $kickoff = $this->calcKickoff($matchDay, $appointment);
                $match->schedule($kickoff);

                $pitchId = $appointment->getPitchId();
                if (!isset($pitches[$pitchId])) {
                    $pitches[$pitchId] = $this->pitchRepository->find($pitchId);
                }
                $match->locate($pitches[$pitchId]);
            }
        }
    }

    /**
     * Returns a closure for sorting matches ascending in amount of possible appointments
     *
     * @param MatchAppointment[] $appointments
     * @return Closure
     */
    private function getMatchSortingClosure(array $appointments): Closure
    {
        return function(MatchEntity $a, MatchEntity $b) use ($appointments) {
            $countA = 0;
            $countB = 0;
            foreach ($appointments as $appointment) {
                if ($this->isPossible($a, $appointment)) {
                    $countA++;
                }
                if ($this->isPossible($b, $appointment)) {
                    $countB++;
                }
            }

            return $countA <=> $countB;
        };
    }

    /**
     * @param MatchDay $matchDay
     * @param MatchAppointment $appointment
     * @return DateTimeImmutable
     */
    private function calcKickoff(MatchDay $matchDay, MatchAppointment $appointment): DateTimeImmutable
    {
        $kickoff = $matchDay->getStartDate();
        $targetDay = $appointment->getKickoff()->format('N');

        for ($i = 0; $i < 7; $i++) {
            if ($kickoff->format('N') === $targetDay) {
                break;
            }
            $kickoff = $kickoff->modify('+ 1 day');
            Assert::true(
                $kickoff <= $matchDay->getEndDate()->setTime(23, 59, 59),
                'Cannot determine kickoff day'
            );
        }

        $h = (int) $appointment->getKickoff()->format('H');
        $m = (int) $appointment->getKickoff()->format('i');
        $s = (int) $appointment->getKickoff()->format('s');

        return $kickoff->setTime($h, $m, $s);
    }

    /**
     * @param MatchEntity $match
     * @param MatchAppointment[] $appointments
     * @return MatchAppointment
     */
    private function findAppointment(MatchEntity $match, array &$appointments): MatchAppointment
    {
        foreach ($appointments as $key => $appointment) {
            if ($this->isPossible($match, $appointment)) {
                unset($appointments[$key]);
                return $appointment;
            }
        }

        throw new DomainException('Cannot find appointment for match ' . $match->getId());
    }

    /**
     * @param MatchEntity $match
     * @param MatchAppointment $appointment
     * @return bool
     */
    private function isPossible(MatchEntity $match, MatchAppointment $appointment): bool
    {
        foreach ($appointment->getUnavailableTeamIds() as $unavailableTeamId) {
            if ($unavailableTeamId === $match->getHomeTeam()->getId() || $unavailableTeamId === $match->getGuestTeam()->getId()) {
                return false;
            }
        }

        return true;
    }
}
