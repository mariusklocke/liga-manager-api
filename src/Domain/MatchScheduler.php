<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain;

use DateTimeImmutable;
use HexagonalPlayground\Domain\Exception\ConflictException;
use HexagonalPlayground\Domain\Exception\NotFoundException;
use HexagonalPlayground\Domain\Value\MatchAppointment;

class MatchScheduler
{
    /**
     * @param MatchDay $matchDay
     * @param MatchAppointment[] $appointments
     * @param Pitch[] $pitches
     */
    public function scheduleMatchesForMatchDay(MatchDay $matchDay, array $appointments, array $pitches): void
    {
        $matches = $matchDay->getMatches();

        count($appointments) >= count($matches) || throw new ConflictException('matchAppointmentsTooLow');

        shuffle($appointments);

        // Sort matches ascending in amount of possible appointments
        usort($matches, function (MatchEntity $a, MatchEntity $b) use ($appointments): int {
            return $this->countPossibleAppointments($a, $appointments) <=> $this->countPossibleAppointments($b, $appointments);
        });

        foreach ($matches as $match) {
            $selectedAppointment = null;

            // Get the first possible appointment and remove from array
            foreach ($appointments as $key => $appointment) {
                if ($this->isPossible($match, $appointment)) {
                    unset($appointments[$key]);
                    $selectedAppointment = $appointment;
                    break;
                }
            }

            $selectedAppointment !== null || throw new ConflictException('noAppointmentPossible', [$match->getId()]);

            $kickoff = $this->calcKickoff($matchDay, $selectedAppointment);

            $match->setKickoff($kickoff);

            $pitch = $pitches[$selectedAppointment->getPitchId()] ?? null;

            $pitch !== null || throw new NotFoundException('pitchNotFound');

            $match->locate($pitch);
        }
    }

    /**
     * @param MatchEntity $match
     * @param MatchAppointment[] $appointments
     * @return int
     */
    private function countPossibleAppointments(MatchEntity $match, array $appointments): int
    {
        $count = 0;

        foreach ($appointments as $appointment) {
            if ($this->isPossible($match, $appointment)) {
                $count++;
            }
        }

        return $count;
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
            $kickoff <= $matchDay->getEndDate()->setTime(23, 59, 59) || throw new ConflictException('impossibleKickoffDay');
        }

        $h = (int) $appointment->getKickoff()->format('H');
        $m = (int) $appointment->getKickoff()->format('i');
        $s = (int) $appointment->getKickoff()->format('s');

        return $kickoff->setTime($h, $m, $s);
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
