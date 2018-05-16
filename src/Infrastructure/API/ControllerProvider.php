<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API;

use HexagonalPlayground\Infrastructure\Persistence\Read\MatchRepository;
use HexagonalPlayground\Infrastructure\Persistence\Read\PitchRepository;
use HexagonalPlayground\Infrastructure\Persistence\Read\RankingRepository;
use HexagonalPlayground\Infrastructure\Persistence\Read\SeasonRepository;
use HexagonalPlayground\Infrastructure\Persistence\Read\TeamRepository;
use HexagonalPlayground\Infrastructure\Persistence\Read\TournamentRepository;
use HexagonalPlayground\Infrastructure\API\Controller\MatchCommandController;
use HexagonalPlayground\Infrastructure\API\Controller\MatchQueryController;
use HexagonalPlayground\Infrastructure\API\Controller\PitchCommandController;
use HexagonalPlayground\Infrastructure\API\Controller\PitchQueryController;
use HexagonalPlayground\Infrastructure\API\Controller\SeasonCommandController;
use HexagonalPlayground\Infrastructure\API\Controller\SeasonQueryController;
use HexagonalPlayground\Infrastructure\API\Controller\TeamCommandController;
use HexagonalPlayground\Infrastructure\API\Controller\TeamQueryController;
use HexagonalPlayground\Infrastructure\API\Controller\TournamentCommandController;
use HexagonalPlayground\Infrastructure\API\Controller\TournamentQueryController;
use HexagonalPlayground\Infrastructure\API\Controller\UserCommandController;
use HexagonalPlayground\Infrastructure\API\Controller\UserQueryController;
use HexagonalPlayground\Application\Security\Authenticator;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ControllerProvider implements ServiceProviderInterface
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
        $container[MatchCommandController::class] = function() use ($container) {
            return new MatchCommandController($container['commandBus']);
        };
        $container[MatchQueryController::class] = function() use ($container) {
            return new MatchQueryController($container[MatchRepository::class]);
        };
        $container[PitchQueryController::class] = function() use ($container) {
            return new PitchQueryController($container[PitchRepository::class]);
        };
        $container[PitchCommandController::class] = function () use ($container) {
            return new PitchCommandController($container['commandBus']);
        };
        $container[SeasonQueryController::class] = function() use ($container) {
            return new SeasonQueryController(
                $container[SeasonRepository::class],
                $container[RankingRepository::class],
                $container[MatchRepository::class]
            );
        };
        $container[SeasonCommandController::class] = function() use ($container) {
            return new SeasonCommandController($container['commandBus']);
        };
        $container[TeamQueryController::class] = function() use ($container) {
            return new TeamQueryController($container[TeamRepository::class]);
        };
        $container[TeamCommandController::class] = function () use ($container) {
            return new TeamCommandController($container['commandBus']);
        };
        $container[TournamentCommandController::class] = function () use ($container) {
            return new TournamentCommandController($container['commandBus']);
        };
        $container[TournamentQueryController::class] = function () use ($container) {
            return new TournamentQueryController($container[TournamentRepository::class]);
        };
        $container[UserCommandController::class] = function () use ($container) {
            return new UserCommandController($container['commandBus']);
        };
        $container[UserQueryController::class] = function () use ($container) {
            return new UserQueryController($container[Authenticator::class]->getAuthenticatedUser());
        };
    }
}