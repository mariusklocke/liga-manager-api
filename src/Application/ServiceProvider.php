<?php declare(strict_types=1);

namespace HexagonalPlayground\Application;

use DI;
use HexagonalPlayground\Application\Bus\ContainerHandlerResolver;
use HexagonalPlayground\Application\Bus\HandlerResolver;
use HexagonalPlayground\Application\Command\AddRankingPenaltyCommand;
use HexagonalPlayground\Application\Command\AddTeamToSeasonCommand;
use HexagonalPlayground\Application\Command\CancelMatchCommand;
use HexagonalPlayground\Application\Command\ChangeUserPasswordCommand;
use HexagonalPlayground\Application\Command\CommandInterface;
use HexagonalPlayground\Application\Command\CreateMatchesForSeasonCommand;
use HexagonalPlayground\Application\Command\CreatePitchCommand;
use HexagonalPlayground\Application\Command\CreateSeasonCommand;
use HexagonalPlayground\Application\Command\CreateTeamCommand;
use HexagonalPlayground\Application\Command\CreateTournamentCommand;
use HexagonalPlayground\Application\Command\CreateUserCommand;
use HexagonalPlayground\Application\Command\DeletePitchCommand;
use HexagonalPlayground\Application\Command\DeleteSeasonCommand;
use HexagonalPlayground\Application\Command\DeleteTeamCommand;
use HexagonalPlayground\Application\Command\DeleteTournamentCommand;
use HexagonalPlayground\Application\Command\DeleteUserCommand;
use HexagonalPlayground\Application\Command\EndSeasonCommand;
use HexagonalPlayground\Application\Command\InvalidateAccessTokensCommand;
use HexagonalPlayground\Application\Command\LocateMatchCommand;
use HexagonalPlayground\Application\Command\RemoveRankingPenaltyCommand;
use HexagonalPlayground\Application\Command\RemoveTeamFromSeasonCommand;
use HexagonalPlayground\Application\Command\RenameTeamCommand;
use HexagonalPlayground\Application\Command\ReplaceTeamInSeasonCommand;
use HexagonalPlayground\Application\Command\RescheduleMatchDayCommand;
use HexagonalPlayground\Application\Command\ScheduleAllMatchesForMatchDayCommand;
use HexagonalPlayground\Application\Command\ScheduleAllMatchesForSeasonCommand;
use HexagonalPlayground\Application\Command\ScheduleMatchCommand;
use HexagonalPlayground\Application\Command\SendInviteMailCommand;
use HexagonalPlayground\Application\Command\SendPasswordResetMailCommand;
use HexagonalPlayground\Application\Command\SetTournamentRoundCommand;
use HexagonalPlayground\Application\Command\StartSeasonCommand;
use HexagonalPlayground\Application\Command\SubmitMatchResultCommand;
use HexagonalPlayground\Application\Command\UpdatePitchContactCommand;
use HexagonalPlayground\Application\Command\UpdateTeamContactCommand;
use HexagonalPlayground\Application\Command\UpdateUserCommand;
use HexagonalPlayground\Application\Command\v2\CommandInterface as v2CommandInterface;
use HexagonalPlayground\Application\Command\v2\CreateMatchCommand;
use HexagonalPlayground\Application\Command\v2\CreateMatchDayCommand;
use HexagonalPlayground\Application\Command\v2\CreateRankingPenaltyCommand;
use HexagonalPlayground\Application\Command\v2\DeleteMatchCommand;
use HexagonalPlayground\Application\Command\v2\DeleteMatchDayCommand;
use HexagonalPlayground\Application\Command\v2\DeleteRankingPenaltyCommand;
use HexagonalPlayground\Application\Command\v2\GenerateMatchDaysCommand;
use HexagonalPlayground\Application\Command\v2\ScheduleMatchesForMatchDayCommand;
use HexagonalPlayground\Application\Command\v2\UpdateMatchDayCommand;
use HexagonalPlayground\Application\Command\v2\UpdatePitchCommand;
use HexagonalPlayground\Application\Command\v2\UpdateSeasonCommand;
use HexagonalPlayground\Application\Command\v2\UpdateTeamCommand;
use HexagonalPlayground\Application\Command\v2\UpdateTournamentCommand;
use HexagonalPlayground\Application\Command\v2\UpdateUserPasswordCommand;

class ServiceProvider implements ServiceProviderInterface
{
    public function getDefinitions(): array
    {
        return [
            CommandInterface::class => [
                AddRankingPenaltyCommand::class,
                AddTeamToSeasonCommand::class,
                CancelMatchCommand::class,
                ChangeUserPasswordCommand::class,
                CreateMatchesForSeasonCommand::class,
                CreatePitchCommand::class,
                CreateSeasonCommand::class,
                CreateTeamCommand::class,
                CreateTournamentCommand::class,
                CreateUserCommand::class,
                DeletePitchCommand::class,
                DeleteSeasonCommand::class,
                DeleteTeamCommand::class,
                DeleteTournamentCommand::class,
                DeleteUserCommand::class,
                EndSeasonCommand::class,
                InvalidateAccessTokensCommand::class,
                LocateMatchCommand::class,
                RemoveRankingPenaltyCommand::class,
                RemoveTeamFromSeasonCommand::class,
                RenameTeamCommand::class,
                ReplaceTeamInSeasonCommand::class,
                RescheduleMatchDayCommand::class,
                ScheduleAllMatchesForMatchDayCommand::class,
                ScheduleAllMatchesForSeasonCommand::class,
                ScheduleMatchCommand::class,
                SendInviteMailCommand::class,
                SendPasswordResetMailCommand::class,
                SetTournamentRoundCommand::class,
                StartSeasonCommand::class,
                SubmitMatchResultCommand::class,
                UpdatePitchContactCommand::class,
                UpdateTeamContactCommand::class,
                UpdateUserCommand::class
            ],
            HandlerResolver::class => DI\get(ContainerHandlerResolver::class),
            v2CommandInterface::class => [
                \HexagonalPlayground\Application\Command\v2\CancelMatchCommand::class,
                CreateMatchCommand::class,
                CreateMatchDayCommand::class,
                \HexagonalPlayground\Application\Command\v2\CreatePitchCommand::class,
                CreateRankingPenaltyCommand::class,
                \HexagonalPlayground\Application\Command\v2\CreateSeasonCommand::class,
                \HexagonalPlayground\Application\Command\v2\CreateTeamCommand::class,
                \HexagonalPlayground\Application\Command\v2\CreateTournamentCommand::class,
                \HexagonalPlayground\Application\Command\v2\CreateUserCommand::class,
                DeleteMatchCommand::class,
                DeleteMatchDayCommand::class,
                \HexagonalPlayground\Application\Command\v2\DeletePitchCommand::class,
                DeleteRankingPenaltyCommand::class,
                \HexagonalPlayground\Application\Command\v2\DeleteSeasonCommand::class,
                \HexagonalPlayground\Application\Command\v2\DeleteTeamCommand::class,
                \HexagonalPlayground\Application\Command\v2\DeleteTournamentCommand::class,
                \HexagonalPlayground\Application\Command\v2\DeleteUserCommand::class,
                GenerateMatchDaysCommand::class,
                \HexagonalPlayground\Application\Command\v2\InvalidateAccessTokensCommand::class,
                \HexagonalPlayground\Application\Command\v2\LocateMatchCommand::class,
                \HexagonalPlayground\Application\Command\v2\ScheduleMatchCommand::class,
                ScheduleMatchesForMatchDayCommand::class,
                \HexagonalPlayground\Application\Command\v2\SendInviteMailCommand::class,
                \HexagonalPlayground\Application\Command\v2\SendPasswordResetMailCommand::class,
                \HexagonalPlayground\Application\Command\v2\SubmitMatchResultCommand::class,
                UpdateMatchDayCommand::class,
                UpdatePitchCommand::class,
                UpdateSeasonCommand::class,
                UpdateTeamCommand::class,
                UpdateSeasonCommand::class,
                UpdateTeamCommand::class,
                UpdateTournamentCommand::class,
                \HexagonalPlayground\Application\Command\v2\UpdateUserCommand::class,
                UpdateUserPasswordCommand::class
            ]
        ];
    }
}
