<?php declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Email\MailerInterface;
use HexagonalPlayground\Application\Security\TokenFactoryInterface;
use HexagonalPlayground\Application\TemplateRendererInterface;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ServiceProvider implements ServiceProviderInterface
{
    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Container $container A container instance
     */
    public function register(Container $container)
    {
        $container[CreateTeamHandler::class] = function () use ($container) {
            return new CreateTeamHandler(
                $container['orm.repository.team']
            );
        };
        $container[DeleteTeamHandler::class] = function() use ($container) {
            return new DeleteTeamHandler($container['orm.repository.team']);
        };
        $container[StartSeasonHandler::class] = function() use ($container) {
            return new StartSeasonHandler($container['orm.repository.season']);
        };
        $container[CreateMatchesForSeasonHandler::class] = function() use ($container) {
            return new CreateMatchesForSeasonHandler(
                $container['orm.repository.season']
            );
        };
        $container[DeleteSeasonHandler::class] = function() use ($container) {
            return new DeleteSeasonHandler($container['orm.repository.season']);
        };
        $container[ScheduleMatchHandler::class] = function() use ($container) {
            return new ScheduleMatchHandler($container['orm.repository.match']);
        };
        $container[SubmitMatchResultHandler::class] = function () use ($container) {
            return new SubmitMatchResultHandler(
                $container['orm.repository.match']
            );
        };
        $container[LocateMatchHandler::class] = function () use ($container) {
            return new LocateMatchHandler(
                $container['orm.repository.match'],
                $container['orm.repository.pitch']
            );
        };
        $container[CancelMatchHandler::class] = function () use ($container) {
            return new CancelMatchHandler($container['orm.repository.match']);
        };
        $container[AddTeamToSeasonHandler::class] = function () use ($container) {
            return new AddTeamToSeasonHandler(
                $container['orm.repository.season'],
                $container['orm.repository.team']
            );
        };
        $container[RemoveTeamFromSeasonHandler::class] = function () use ($container) {
            return new RemoveTeamFromSeasonHandler(
                $container['orm.repository.season'],
                $container['orm.repository.team']
            );
        };
        $container[CreateSeasonHandler::class] = function () use ($container) {
            return new CreateSeasonHandler($container['orm.repository.season']);
        };
        $container[CreateTournamentHandler::class] = function () use ($container) {
            return new CreateTournamentHandler($container['orm.repository.tournament']);
        };
        $container[SetTournamentRoundHandler::class] = function () use ($container) {
            return new SetTournamentRoundHandler(
                $container['orm.repository.tournament'],
                $container['orm.repository.team']
            );
        };
        $container[ChangeUserPasswordHandler::class] = function () {
            return new ChangeUserPasswordHandler();
        };
        $container[CreateUserHandler::class] = function () use ($container) {
            return new CreateUserHandler(
                $container['orm.repository.user'],
                $container['orm.repository.team']
            );
        };
        $container[CreatePitchHandler::class] = function () use ($container) {
            return new CreatePitchHandler(
                $container['orm.repository.pitch']
            );
        };
        $container[UpdateTeamContactHandler::class] = function () use ($container) {
            return new UpdateTeamContactHandler($container['orm.repository.team']);
        };
        $container[UpdatePitchContactHandler::class] = function () use ($container) {
            return new UpdatePitchContactHandler($container['orm.repository.pitch']);
        };
        $container[SendPasswordResetMailHandler::class] = function () use ($container) {
            return new SendPasswordResetMailHandler(
                $container[TokenFactoryInterface::class],
                $container['orm.repository.user'],
                $container[TemplateRendererInterface::class],
                $container[MailerInterface::class]
            );
        };
        $container[RescheduleMatchDayHandler::class] = function () use ($container) {
            return new RescheduleMatchDayHandler($container['orm.repository.matchDay']);
        };
        $container[DeleteTournamentHandler::class] = function () use ($container) {
            return new DeleteTournamentHandler($container['orm.repository.tournament']);
        };
        $container[DeleteUserHandler::class] = function () use ($container) {
            return new DeleteUserHandler($container['orm.repository.user']);
        };
        $container[EndSeasonHandler::class] = function () use ($container) {
            return new EndSeasonHandler($container['orm.repository.season']);
        };
        $container[AddRankingPenaltyHandler::class] = function () use ($container) {
            return new AddRankingPenaltyHandler($container['orm.repository.season'], $container['orm.repository.team']);
        };
        $container[RemoveRankingPenaltyHandler::class] = function () use ($container) {
            return new RemoveRankingPenaltyHandler($container['orm.repository.season']);
        };
        $container[RenameTeamHandler::class] = function () use ($container) {
            return new RenameTeamHandler($container['orm.repository.team']);
        };
        $container[UpdateUserHandler::class] = function () use ($container) {
            return new UpdateUserHandler($container['orm.repository.user'], $container['orm.repository.team']);
        };
        $container[DeletePitchHandler::class] = function () use ($container) {
            return new DeletePitchHandler($container['orm.repository.pitch']);
        };
        $container[SendInviteMailHandler::class] = function () use ($container) {
            return new SendInviteMailHandler(
                $container[TokenFactoryInterface::class],
                $container['orm.repository.user'],
                $container[TemplateRendererInterface::class],
                $container[MailerInterface::class]
            );
        };
        $container[InvalidateAccessTokensHandler::class] = function () use ($container) {
            return new InvalidateAccessTokensHandler($container['orm.repository.user']);
        };
        $container[ScheduleAllMatchesForSeasonHandler::class] = function () use ($container) {
            return new ScheduleAllMatchesForSeasonHandler(
                $container['orm.repository.season'],
                $container['orm.repository.pitch']
            );
        };
    }
}
