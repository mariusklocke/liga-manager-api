<?php

namespace HexagonalDream\Application\Handler;

use HexagonalDream\Application\Exception\MatchMakingException;
use HexagonalDream\Application\Command\CreateMatchDaysCommand;
use HexagonalDream\Domain\Match;
use HexagonalDream\Domain\MatchDay;
use HexagonalDream\Domain\Team;
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
     * @return MatchDay[]
     * @throws MatchMakingException
     */
    public function handle(CreateMatchDaysCommand $command)
    {
        $opponents = $this->generateOpponentMatrix($command->getTeams());
        $allTeams = [];
        foreach ($command->getTeams() as $team) {
            $allTeams[$team->getId()] = $team;
        }

        $shuffledTeamIds = array_keys($allTeams);
        shuffle($shuffledTeamIds);

        $matchDayList = [];
        $matchDayCount = count($allTeams) - 1;
        for ($i = 0; $i < $matchDayCount; $i++) {
            $matchDay   = new MatchDay($command->getSeason(), $i + 1);
            $matchCount = (int) floor(count($allTeams) / 2);
            $remainingTeamIds = array_keys($allTeams);
            shuffle($remainingTeamIds);
            for ($j = 0; $j < $matchCount; $j++) {
                $homeTeamId = array_pop($remainingTeamIds);
                if (null === $homeTeamId) {
                    throw new MatchMakingException();
                }
                $possibleOpponents = array_intersect(array_keys($opponents[$homeTeamId]), $remainingTeamIds);
                if (empty($possibleOpponents)) {
                    var_dump($opponents[$homeTeamId]);
                    var_dump($remainingTeamIds);
                    var_dump([$i, $j]);
                    throw new MatchMakingException();
                }
                shuffle($possibleOpponents);
                $guestTeamId = array_pop($possibleOpponents);

                if (($key = array_search($guestTeamId, $remainingTeamIds)) !== false) {
                    unset($remainingTeamIds[$key]);
                }
                unset($opponents[$homeTeamId][$guestTeamId]);
                unset($opponents[$guestTeamId][$homeTeamId]);

                $matchDay->addMatch(
                    new Match($this->uuidGenerator, $matchDay, $allTeams[$homeTeamId], $allTeams[$guestTeamId])
                );
            }
            $matchDayList[] = $matchDay;
        }

        return $matchDayList;
    }

    /**
     * @param Team[] $teams
     * @return array
     */
    private function generateOpponentMatrix(array $teams)
    {
        $opponents = [];
        $teams = array_values($teams);
        $count = count($teams);
        for ($i = 0; $i < $count; $i++) {
            $opponents[$teams[$i]->getId()] = [];
            for ($j = 0; $j < $count; $j++) {
                if ($i != $j) {
                    $opponents[$teams[$i]->getId()][$teams[$j]->getId()] = true;
                }
            }
        }

        return $opponents;
    }
}
