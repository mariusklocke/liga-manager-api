<?php

namespace HexagonalDream\Application\Handler;

use HexagonalDream\Application\Exception\MatchMakingException;
use HexagonalDream\Application\Command\CreateMatchDaysCommand;
use HexagonalDream\Domain\Match;
use HexagonalDream\Domain\Season;
use HexagonalDream\Domain\UuidGeneratorInterface;

class CreateMatchDaysHandler
{
    /** @var UuidGeneratorInterface */
    private $uuidGenerator;

    public function __construct(UuidGeneratorInterface $uuidGenerator)
    {
        $this->uuidGenerator = $uuidGenerator;
    }

    /**
     * @param CreateMatchDaysCommand $command
     * @return Match[]
     * @throws MatchMakingException
     */
    public function handle(CreateMatchDaysCommand $command)
    {

    }

    /**
     * Implements a match day generation algorithm
     *
     * Based on: https://de.wikipedia.org/wiki/Spielplan_(Sport)
     *
     * @param Season $season
     * @return Match[]
     * @throws MatchMakingException
     */
    public function createMatchDays(Season $season)
    {
        $shuffledTeams = array_values($season->getTeams());
        shuffle($shuffledTeams);
        if (count($shuffledTeams) % 2 != 0) {
            $shuffledTeams[] = null;
        }

        $matchDayCount = count($shuffledTeams) - 1;
        $matchList = [];
        for ($n = 1; $n <= $matchDayCount; $n++) {
            $teams = $shuffledTeams; // copy array
            for ($k = 1; $k < count($shuffledTeams); $k++) {
                for ($l = 1; $l < $k; $l++) {
                    if (($k + $l) % $matchDayCount == ($n % $matchDayCount)) {
                        $sumIsEven = (($k + $l) % 2 == 0);
                        $homeTeam = $sumIsEven ? $teams[$k-1] : $teams[$l-1];
                        $guestTeam = $sumIsEven ? $teams[$l-1] : $teams[$k-1];
                        if (null !== $homeTeam && null !== $guestTeam) {
                            $matchList[] = new Match($this->uuidGenerator, $season, $n, $homeTeam, $guestTeam);
                        }
                        unset($teams[$k-1]);
                        unset($teams[$l-1]);
                    }
                }
            }

            if (count($teams) != 2) {
                throw new MatchMakingException('Matchday algorithm error');
            }

            $k = max(array_keys($teams));
            $l = min(array_keys($teams));
            $homeTeam = $l+1 > $matchDayCount/2 ? $teams[$k] : $teams[$l];
            $guestTeam = $l+1 > $matchDayCount/2 ? $teams[$l] : $teams[$k];
            if (null !== $homeTeam && null !== $guestTeam) {
                $matchList[] = new Match($this->uuidGenerator, $season, $n, $homeTeam, $guestTeam);
            }
        }

        return $matchList;
    }
}
