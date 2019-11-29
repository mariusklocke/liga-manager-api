<?php declare(strict_types=1);

namespace HexagonalPlayground\Application\Import;

use DateTimeImmutable;
use HexagonalPlayground\Application\Bus\CommandQueue;
use HexagonalPlayground\Application\Command\CreateMatchCommand;
use HexagonalPlayground\Application\Command\CreateMatchDayForSeasonCommand;
use HexagonalPlayground\Application\Command\EndSeasonCommand;
use HexagonalPlayground\Application\Command\ScheduleMatchCommand;
use HexagonalPlayground\Application\Command\StartSeasonCommand;
use HexagonalPlayground\Application\Command\SubmitMatchResultCommand;
use HexagonalPlayground\Application\InputParser;
use HexagonalPlayground\Domain\Value\DatePeriod;

class MatchImporter
{
    public function import(L98IniParser $parser, CommandQueue $queue, string $seasonId, array $teamIdMap): void
    {
        $matchDayIndex = 1;
        $matchUpdateCommands = [];
        while ($round = $parser->getSection(sprintf('Round%d', $matchDayIndex))) {
            $datePeriod = new DatePeriod(
                InputParser::parseDate($round['D1']),
                InputParser::parseDate($round['D2'])
            );
            $command = new CreateMatchDayForSeasonCommand(null, $seasonId, $matchDayIndex, $datePeriod);
            $queue->add($command);
            $matchDayId = $command->getId();

            $matchIndex = 1;
            while (isset($round['TA' . $matchIndex])) {
                /** @var string|null $homeTeamId */
                $homeTeamId  = $teamIdMap[InputParser::parseInteger($round['TA' . $matchIndex])] ?? null;
                /** @var string|null $guestTeamId */
                $guestTeamId = $teamIdMap[InputParser::parseInteger($round['TB' . $matchIndex])] ?? null;
                if (null !== $homeTeamId && null !== $guestTeamId) {

                    $command = new CreateMatchCommand(null, $matchDayId, $homeTeamId, $guestTeamId);
                    $queue->add($command);

                    $matchId = $command->getId();
                    $homeScore = InputParser::parseInteger($round['GA' . $matchIndex]);
                    $guestScore = InputParser::parseInteger($round['GB' . $matchIndex]);

                    if ($homeScore >= 0 || $guestScore >= 0) {
                        $matchUpdateCommands[] = new SubmitMatchResultCommand($matchId, $homeScore, $guestScore);
                    }

                    $kickoff = $round['AT' . $matchIndex] !== '' ? InputParser::parseInteger($round['AT' . $matchIndex]) : null;
                    if (null !== $kickoff) {
                        $matchUpdateCommands[] = new ScheduleMatchCommand(
                            $matchId,
                            new DateTimeImmutable('@' . $kickoff)
                        );
                    }
                }
                $matchIndex++;
            }

            $matchDayIndex++;
        }

        $queue->add(new StartSeasonCommand($seasonId));

        foreach ($matchUpdateCommands as $command) {
            $queue->add($command);
        }

        $queue->add(new EndSeasonCommand($seasonId));
    }
}