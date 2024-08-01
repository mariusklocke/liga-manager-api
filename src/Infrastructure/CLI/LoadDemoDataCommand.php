<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use DateTimeImmutable;
use HexagonalPlayground\Application\Bus\CommandBus;
use HexagonalPlayground\Application\Command\AddTeamToSeasonCommand;
use HexagonalPlayground\Application\Command\CreateMatchesForSeasonCommand;
use HexagonalPlayground\Application\Command\CreatePitchCommand;
use HexagonalPlayground\Application\Command\CreateSeasonCommand;
use HexagonalPlayground\Application\Command\CreateTeamCommand;
use HexagonalPlayground\Application\Command\CreateTournamentCommand;
use HexagonalPlayground\Application\Command\CreateUserCommand;
use HexagonalPlayground\Application\Command\ScheduleAllMatchesForSeasonCommand;
use HexagonalPlayground\Application\Command\SetTournamentRoundCommand;
use HexagonalPlayground\Application\Command\StartSeasonCommand;
use HexagonalPlayground\Domain\Value\DatePeriod;
use HexagonalPlayground\Domain\Value\MatchAppointment;
use HexagonalPlayground\Domain\Value\TeamIdPair;
use HexagonalPlayground\Domain\User;
use Iterator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LoadDemoDataCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('app:db:demo-data');
        $this->setDescription('Load demo data into current database');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $seasonIds = [];
        foreach ($this->createSeasons() as $seasonId) {
            $seasonIds[] = $seasonId;
        }

        $teamIds = [];
        foreach ($this->createTeams() as $teamId) {
            $teamIds[] = $teamId;
        }

        $this->createTeamManagers($teamIds);

        $pitchIds = [];
        foreach ($this->createPitches() as $pitchId) {
            $pitchIds[] = $pitchId;
        }

        $this->linkTeamsWithSeasons($teamIds, $seasonIds);
        $this->startSeason($seasonIds[0], count($teamIds));
        $this->scheduleMatches($seasonIds[0], $teamIds, $pitchIds);

        $tournamentIds = [];
        foreach ($this->createTournaments() as $tournamentId) {
            $tournamentIds[] = $tournamentId;
        }

        $this->createTournamentRounds($tournamentIds, $teamIds);
        $output->writeln('Fixtures successfully loaded');

        return 0;
    }

    /**
     * @return Iterator|string[]
     */
    private function createTournaments(): Iterator
    {
        foreach (['A', 'B', 'C'] as $char) {
            $command = new CreateTournamentCommand(null, 'Tournament ' . $char);
            $this->getCommandBus()->execute($command, $this->getAuthContext());
            yield $command->getId();
        }
    }

    private function createTournamentRounds(array $tournamentIds, array $teamIds): void
    {
        foreach ($tournamentIds as $tournamentId) {
            $pairs = [];
            foreach (array_chunk($teamIds, 2) as $chunk) {
                if (count($chunk) === 2) {
                    $pairs[] = new TeamIdPair($chunk[0], $chunk[1]);
                }
            }
            $start   = new DateTimeImmutable('next saturday');
            $period  = new DatePeriod($start, $start->modify('next sunday'));
            $command = new SetTournamentRoundCommand($tournamentId, 1, $pairs, $period);
            $this->getCommandBus()->execute($command, $this->getAuthContext());
        }
    }

    /**
     * @return Iterator|string[]
     */
    private function createSeasons(): Iterator
    {
        $years = ['17/18', '18/19', '19/20'];
        foreach ($years as $year) {
            $command = new CreateSeasonCommand(null, 'Season ' . $year);
            $this->getCommandBus()->execute($command, $this->getAuthContext());
            yield $command->getId();
        }
    }

    /**
     * @return Iterator|string[]
     */
    private function createPitches(): Iterator
    {
        $colors = ['Red', 'Blue'];
        foreach ($colors as $color) {
            $command = new CreatePitchCommand(null, 'Pitch ' . $color, 12.34, 23.45);
            $this->getCommandBus()->execute($command, $this->getAuthContext());
            yield $command->getId();
        }
    }

    /**
     * @return Iterator|string[]
     */
    private function createTeams(): Iterator
    {
        for ($i = 1; $i <= 8; $i++) {
            $teamName = sprintf('Team No. %02d', $i);
            $command  = new CreateTeamCommand(null, $teamName);
            $this->getCommandBus()->execute($command, $this->getAuthContext());
            yield $command->getId();
        }
    }

    /**
     * @param array $teamIds
     */
    private function createTeamManagers(array $teamIds): void
    {
        for ($i = 1; $i <= 8; $i++) {
            $command = new CreateUserCommand(
                null,
                'user' . $i . "@example.com",
                '123456',
                'user' . $i,
                'admin' . $i,
                User::ROLE_TEAM_MANAGER,
                [array_shift($teamIds)]
            );

            $this->getCommandBus()->execute($command, $this->getAuthContext());
        }
    }

    /**
     * @param array $teamIds
     * @param array $seasonIds
     */
    private function linkTeamsWithSeasons(array $teamIds, array $seasonIds): void
    {
        foreach ($seasonIds as $seasonId) {
            foreach ($teamIds as $teamId) {
                $command = new AddTeamToSeasonCommand($seasonId, $teamId);
                $this->getCommandBus()->execute($command, $this->getAuthContext());
            }
        }
    }

    /**
     * @param string $seasonId
     * @param int $teamCount
     */
    private function startSeason(string $seasonId, int $teamCount): void
    {
        $command = new CreateMatchesForSeasonCommand($seasonId, $this->generateMatchDayDates($teamCount));
        $this->getCommandBus()->execute($command, $this->getAuthContext());
        $command = new StartSeasonCommand($seasonId);
        $this->getCommandBus()->execute($command, $this->getAuthContext());
    }

    /**
     * @param string $seasonId
     * @param array $teamIds
     * @param array $pitchIds
     */
    private function scheduleMatches(string $seasonId, array $teamIds, array $pitchIds): void
    {
        $command = new ScheduleAllMatchesForSeasonCommand(
            $seasonId,
            $this->generateMatchAppointments($teamIds, $pitchIds)
        );
        $this->getCommandBus()->execute($command, $this->getAuthContext());
    }

    /**
     * @param int $teamCount
     * @return array
     */
    private function generateMatchDayDates(int $teamCount): array
    {
        $start = new DateTimeImmutable('next saturday');
        $end   = $start->modify('next sunday');
        $result = [];
        $n = $teamCount - 1;
        for ($i = 0; $i < $n; $i++) {
            $start = $start->modify('+7 days');
            $end   = $end->modify('+7 days');
            $result[] = new DatePeriod($start, $end);
        }

        return $result;
    }

    /**
     * @param array $teamIds
     * @param array $pitchIds
     * @return array
     */
    private function generateMatchAppointments(array $teamIds, array $pitchIds): array
    {
        $appointments = [];
        $saturday = new DateTimeImmutable('next saturday');
        $sunday = $saturday->modify('+1 day');

        $appointments[] = new MatchAppointment(
            $saturday->setTime(15, 30),
            [],
            $pitchIds[0]
        );

        $appointments[] = new MatchAppointment(
            $saturday->setTime(17, 30),
            [$teamIds[0], $teamIds[1]],
            $pitchIds[1]
        );

        $appointments[] = new MatchAppointment(
            $sunday->setTime(12, 00),
            [$teamIds[2]],
            $pitchIds[0]
        );

        $appointments[] = new MatchAppointment(
            $sunday->setTime(14, 00),
            [],
            $pitchIds[1]
        );

        return $appointments;
    }

    private function getCommandBus(): CommandBus
    {
        return $this->container->get(CommandBus::class);
    }
}
