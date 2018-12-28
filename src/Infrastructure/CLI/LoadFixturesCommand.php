<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\CLI;

use HexagonalPlayground\Application\Bus\CommandBus;
use HexagonalPlayground\Application\Command\AddTeamToSeasonCommand;
use HexagonalPlayground\Application\Command\CreateMatchesForSeasonCommand;
use HexagonalPlayground\Application\Command\CreatePitchCommand;
use HexagonalPlayground\Application\Command\CreateSeasonCommand;
use HexagonalPlayground\Application\Command\CreateTeamCommand;
use HexagonalPlayground\Application\Command\CreateUserCommand;
use HexagonalPlayground\Application\Command\StartSeasonCommand;
use HexagonalPlayground\Application\Value\DatePeriod;
use HexagonalPlayground\Domain\User;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LoadFixturesCommand extends Command
{
    /** @var CommandBus */
    private $commandBus;

    /**
     * @param CommandBus $commandBus
     */
    public function __construct(CommandBus $commandBus)
    {
        $this->commandBus = $commandBus;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('app:load-fixtures');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->createAdmin();
        $seasonIds = $this->createSeasons();
        $teamIds   = $this->createTeams();
        $this->createTeamManagers($teamIds);
        $this->createPitches();
        $this->linkTeamsWithSeasons($teamIds, $seasonIds);
        $this->startSeason(array_shift($seasonIds), count($teamIds));
        $output->writeln('Fixtures successfully loaded');
        return parent::execute($input, $output);
    }

    private function createSeasons(): array
    {
        $years = ['17/18', '18/19', '19/20'];
        $ids   = [];
        foreach ($years as $year) {
            $command = new CreateSeasonCommand('Season ' . $year);
            $ids[] = $this->commandBus->execute($command->withAuthenticatedUser($this->getCliUser()));
        }

        return $ids;
    }

    private function createPitches(): void
    {
        $colors = ['Red', 'Blue'];
        foreach ($colors as $color) {
            $command = new CreatePitchCommand('Pitch ' . $color, 12.34, 23.45);
            $this->commandBus->execute($command->withAuthenticatedUser($this->getCliUser()));
        }
    }

    private function createTeams(): array
    {
        $ids = [];
        for ($i = 1; $i <= 8; $i++) {
            $teamName = sprintf('Team No. %02d', $i);
            $command  = new CreateTeamCommand($teamName);
            $ids[] = $this->commandBus->execute($command->withAuthenticatedUser($this->getCliUser()));
        }

        return $ids;
    }

    private function createAdmin(): void
    {
        $command = new CreateUserCommand(
            'admin@example.com',
            '123456',
            'admin',
            'admin',
            User::ROLE_ADMIN,
            []
        );
        $this->commandBus->execute($command->withAuthenticatedUser($this->getCliUser()));
    }

    private function createTeamManagers(array $teamIds): void
    {
        for ($i = 1; $i <= 8; $i++) {
            $command = new CreateUserCommand(
                'user' . $i . "@example.com",
                '123456',
                'user' . $i,
                'admin' . $i,
                User::ROLE_TEAM_MANAGER,
                [array_shift($teamIds)]
            );

            $this->commandBus->execute($command->withAuthenticatedUser($this->getCliUser()));
        }
    }

    private function linkTeamsWithSeasons(array $teamIds, array $seasonIds): void
    {
        foreach ($seasonIds as $seasonId) {
            foreach ($teamIds as $teamId) {
                $command = new AddTeamToSeasonCommand($seasonId, $teamId);
                $this->commandBus->execute($command->withAuthenticatedUser($this->getCliUser()));
            }
        }
    }

    private function startSeason(string $seasonId, int $teamCount): void
    {
        $command = new CreateMatchesForSeasonCommand($seasonId, $this->generateMatchDayDates($teamCount));
        $this->commandBus->execute($command->withAuthenticatedUser($this->getCliUser()));
        $command = new StartSeasonCommand($seasonId);
        $this->commandBus->execute($command->withAuthenticatedUser($this->getCliUser()));
    }

    private function generateMatchDayDates(int $teamCount): array
    {
        $start = new \DateTimeImmutable('next saturday');
        $end   = $start->modify('next sunday');
        $result = [];
        $n = $teamCount - 1;
        for ($i = 0; $i < $n; $i++) {
            $start = $start->modify('+7 days');
            $end   = $end->modify('+7 days');
            $result[] = ['from' => $start->format(DATE_ATOM), 'to' => $end->format(DATE_ATOM)];
        }

        return $result;
    }
}
