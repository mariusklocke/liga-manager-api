<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure;

use HexagonalPlayground\Application\Bus\BatchCommandBus;
use HexagonalPlayground\Application\Bus\HandlerResolver;
use HexagonalPlayground\Application\Bus\SingleCommandBus;
use HexagonalPlayground\Application\Command\AddTeamToSeasonCommand;
use HexagonalPlayground\Application\Command\CancelMatchCommand;
use HexagonalPlayground\Application\Command\ChangeUserPasswordCommand;
use HexagonalPlayground\Application\Command\CreateMatchesForSeasonCommand;
use HexagonalPlayground\Application\Command\CreatePitchCommand;
use HexagonalPlayground\Application\Command\CreateSeasonCommand;
use HexagonalPlayground\Application\Command\CreateSingleMatchCommand;
use HexagonalPlayground\Application\Command\CreateTeamCommand;
use HexagonalPlayground\Application\Command\CreateTournamentCommand;
use HexagonalPlayground\Application\Command\CreateUserCommand;
use HexagonalPlayground\Application\Command\DeleteSeasonCommand;
use HexagonalPlayground\Application\Command\DeleteTeamCommand;
use HexagonalPlayground\Application\Command\LoadFixturesCommand;
use HexagonalPlayground\Application\Command\LocateMatchCommand;
use HexagonalPlayground\Application\Command\RemoveTeamFromSeasonCommand;
use HexagonalPlayground\Application\Command\SendPasswordResetMailCommand;
use HexagonalPlayground\Application\Command\ScheduleMatchCommand;
use HexagonalPlayground\Application\Command\SetTournamentRoundCommand;
use HexagonalPlayground\Application\Command\StartSeasonCommand;
use HexagonalPlayground\Application\Command\SubmitMatchResultCommand;
use HexagonalPlayground\Application\Command\UpdatePitchContactCommand;
use HexagonalPlayground\Application\Command\UpdateTeamContactCommand;
use HexagonalPlayground\Application\Email\MailerInterface;
use HexagonalPlayground\Application\FixtureGenerator;
use HexagonalPlayground\Application\Handler\AddTeamToSeasonHandler;
use HexagonalPlayground\Application\Handler\CancelMatchHandler;
use HexagonalPlayground\Application\Handler\ChangeUserPasswordHandler;
use HexagonalPlayground\Application\Handler\CreateMatchesForSeasonHandler;
use HexagonalPlayground\Application\Handler\CreatePitchHandler;
use HexagonalPlayground\Application\Handler\CreateSeasonHandler;
use HexagonalPlayground\Application\Handler\CreateSingleMatchHandler;
use HexagonalPlayground\Application\Handler\CreateTeamHandler;
use HexagonalPlayground\Application\Handler\CreateTournamentHandler;
use HexagonalPlayground\Application\Handler\CreateUserHandler;
use HexagonalPlayground\Application\Handler\DeleteSeasonHandler;
use HexagonalPlayground\Application\Handler\DeleteTeamHandler;
use HexagonalPlayground\Application\Handler\LoadFixturesHandler;
use HexagonalPlayground\Application\Handler\LocateMatchHandler;
use HexagonalPlayground\Application\Handler\RemoveTeamFromSeasonHandler;
use HexagonalPlayground\Application\Handler\SendPasswordResetMailHandler;
use HexagonalPlayground\Application\Handler\ScheduleMatchHandler;
use HexagonalPlayground\Application\Handler\SetTournamentRoundHandler;
use HexagonalPlayground\Application\Handler\StartSeasonHandler;
use HexagonalPlayground\Application\Handler\SubmitMatchResultHandler;
use HexagonalPlayground\Application\Handler\UpdatePitchContactHandler;
use HexagonalPlayground\Application\Handler\UpdateTeamContactHandler;
use HexagonalPlayground\Application\OrmTransactionWrapperInterface;
use HexagonalPlayground\Application\Security\TokenFactoryInterface;
use HexagonalPlayground\Domain\MatchFactory;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class CommandBusProvider implements ServiceProviderInterface
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
        $container[HandlerResolver::class] = function () use ($container) {
            /** @var \Slim\Container $container */
            return new CommandHandlerResolver($container);
        };
        $container['commandBus'] = function() use ($container) {
            return new SingleCommandBus($container[HandlerResolver::class], $container[OrmTransactionWrapperInterface::class]);
        };
        $container['batchCommandBus'] = function () use ($container) {
            return new BatchCommandBus($container[HandlerResolver::class], $container[OrmTransactionWrapperInterface::class]);
        };
        $container[CreateTeamCommand::class] = function () use ($container) {
            return new CreateTeamHandler(
                $container['orm.repository.team']
            );
        };
        $container[DeleteTeamCommand::class] = function() use ($container) {
            return new DeleteTeamHandler($container['orm.repository.team']);
        };
        $container[StartSeasonCommand::class] = function() use ($container) {
            return new StartSeasonHandler($container['orm.repository.season']);
        };
        $container[CreateMatchesForSeasonCommand::class] = function() use ($container) {
            return new CreateMatchesForSeasonHandler(
                new MatchFactory(),
                $container['orm.repository.season'],
                $container['orm.repository.match']
            );
        };
        $container[CreateSingleMatchCommand::class] = function() use ($container) {
            return new CreateSingleMatchHandler(
                new MatchFactory(),
                $container['orm.repository.match'],
                $container['orm.repository.team'],
                $container['orm.repository.season']
            );
        };
        $container[DeleteSeasonCommand::class] = function() use ($container) {
            return new DeleteSeasonHandler($container['orm.repository.season']);
        };
        $container[ScheduleMatchCommand::class] = function() use ($container) {
            return new ScheduleMatchHandler($container['orm.repository.match']);
        };
        $container[SubmitMatchResultCommand::class] = function () use ($container) {
            return new SubmitMatchResultHandler(
                $container['orm.repository.match']
            );
        };
        $container[LocateMatchCommand::class] = function () use ($container) {
            return new LocateMatchHandler(
                $container['orm.repository.match'],
                $container['orm.repository.pitch']
            );
        };
        $container[CancelMatchCommand::class] = function () use ($container) {
            return new CancelMatchHandler($container['orm.repository.match']);
        };
        $container[AddTeamToSeasonCommand::class] = function () use ($container) {
            return new AddTeamToSeasonHandler(
                $container['orm.repository.season'],
                $container['orm.repository.team']
            );
        };
        $container[RemoveTeamFromSeasonCommand::class] = function () use ($container) {
            return new RemoveTeamFromSeasonHandler(
                $container['orm.repository.season'],
                $container['orm.repository.team']
            );
        };
        $container[CreateSeasonCommand::class] = function () use ($container) {
            return new CreateSeasonHandler($container['orm.repository.season']);
        };
        $container[CreateTournamentCommand::class] = function () use ($container) {
            return new CreateTournamentHandler($container['orm.repository.tournament']);
        };
        $container[SetTournamentRoundCommand::class] = function () use ($container) {
            return new SetTournamentRoundHandler(
                new MatchFactory(),
                $container['orm.repository.tournament'],
                $container['orm.repository.match'],
                $container['orm.repository.team']
            );
        };
        $container[ChangeUserPasswordCommand::class] = function () use ($container) {
            return new ChangeUserPasswordHandler();
        };
        $container[CreateUserCommand::class] = function () use ($container) {
            return new CreateUserHandler(
                $container['orm.repository.user'],
                $container['orm.repository.team']
            );
        };
        $container[CreatePitchCommand::class] = function () use ($container) {
            return new CreatePitchHandler(
                $container['orm.repository.pitch']
            );
        };
        $container[UpdateTeamContactCommand::class] = function () use ($container) {
            return new UpdateTeamContactHandler($container['orm.repository.team']);
        };
        $container[UpdatePitchContactCommand::class] = function () use ($container) {
            return new UpdatePitchContactHandler($container['orm.repository.pitch']);
        };
        $container[LoadFixturesCommand::class] = function () use ($container) {
            return new LoadFixturesHandler(
                $container['orm.repository.team'],
                $container['orm.repository.season'],
                $container['orm.repository.pitch'],
                $container['orm.repository.user'],
                new FixtureGenerator()
            );
        };
        $container[SendPasswordResetMailCommand::class] = function () use ($container) {
            return new SendPasswordResetMailHandler(
                $container[TokenFactoryInterface::class],
                $container['orm.repository.user'],
                $container[TemplateRenderer::class],
                $container[MailerInterface::class]
            );
        };
    }
}