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
use HexagonalPlayground\Application\Command\CreateMatchCommand;
use HexagonalPlayground\Application\Command\CreateMatchDayForSeasonCommand;
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
use HexagonalPlayground\Application\Command\EndTournamentCommand;
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
use HexagonalPlayground\Application\Command\StartTournamentCommand;
use HexagonalPlayground\Application\Command\SubmitMatchResultCommand;
use HexagonalPlayground\Application\Command\UpdatePitchContactCommand;
use HexagonalPlayground\Application\Command\UpdateTeamContactCommand;
use HexagonalPlayground\Application\Command\UpdateUserCommand;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

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
                CreateMatchCommand::class,
                CreateMatchDayForSeasonCommand::class,
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
                EndTournamentCommand::class,
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
                StartTournamentCommand::class,
                SubmitMatchResultCommand::class,
                UpdatePitchContactCommand::class,
                UpdateTeamContactCommand::class,
                UpdateUserCommand::class
            ],
            HandlerResolver::class => DI\get(ContainerHandlerResolver::class),
            EventDispatcherInterface::class => DI\factory(function (ContainerInterface $container) {
                $eventDispatcher = new EventDispatcher();

                foreach ($container->get(EventSubscriberInterface::class) as $subscriber) {
                    $eventDispatcher->addSubscriber($subscriber);
                }

                return $eventDispatcher;
            }),
            EventSubscriberInterface::class => []
        ];
    }
}
