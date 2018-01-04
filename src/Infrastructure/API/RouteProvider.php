<?php
/**
 * RouteProvider.php
 *
 * @author    Marius Klocke <marius.klocke@eventim.de>
 * @copyright Copyright (c) 2017, CTS EVENTIM Solutions GmbH
 */

namespace HexagonalDream\Infrastructure\API;

use HexagonalDream\Infrastructure\API\Controller\MatchQueryController;
use HexagonalDream\Infrastructure\API\Controller\PitchQueryController;
use HexagonalDream\Infrastructure\API\Controller\SeasonCommandController;
use HexagonalDream\Infrastructure\API\Controller\SeasonQueryController;
use HexagonalDream\Infrastructure\API\Controller\TeamCommandController;
use HexagonalDream\Infrastructure\API\Controller\TeamQueryController;
use Slim\App;

class RouteProvider
{
    public function registerRoutes(App $app)
    {
        $container = $app->getContainer();
        $app->get('/team', function () use ($container) {
            /** @var TeamQueryController $controller */
            $controller = $container[TeamQueryController::class];
            return $controller->findAllTeams();
        });
        $app->get('/team/{id}', function ($request, $response, $args) use ($container) {
            /** @var TeamQueryController $controller */
            $controller = $container[TeamQueryController::class];
            return $controller->findTeamById($args['id']);
        });
        $app->get('/season/{id}/team', function ($request, $response, $args) use ($container) {
            /** @var TeamQueryController $controller */
            $controller = $container[TeamQueryController::class];
            return $controller->findTeamsBySeasonId($args['id']);
        });
        $app->post('/team', function ($request) use ($container) {
            /** @var TeamCommandController $controller */
            $controller = $container[TeamCommandController::class];
            return $controller->create($request);
        });
        $app->delete('/team/{id}', function ($request, $response, $args) use ($container) {
            /** @var TeamCommandController $controller */
            $controller = $container[TeamCommandController::class];
            return $controller->delete($args['id']);
        });
        $app->get('/season', function () use ($container) {
            /** @var SeasonQueryController $controller */
            $controller = $container[SeasonQueryController::class];
            return $controller->findAllSeasons();
        });
        $app->get('/season/{id}', function ($request, $response, $args) use ($container) {
            /** @var SeasonQueryController $controller */
            $controller = $container[SeasonQueryController::class];
            return $controller->findSeasonById($args['id']);
        });
        $app->get('/season/{id}/ranking', function ($request, $response, $args) use ($container) {
            /** @var SeasonQueryController $controller */
            $controller = $container[SeasonQueryController::class];
            return $controller->findRanking($args['id']);
        });
        $app->get('/season/{seasonId}/matches/{matchDay}', function ($request, $response, $args) use ($container) {
            /** @var MatchQueryController $controller */
            $controller = $container[MatchQueryController::class];
            return $controller->findMatches($args['seasonId'], (int)$args['matchDay']);
        });
        $app->get('/match/{id}', function ($request, $response, $args) use ($container) {
            /** @var MatchQueryController $controller */
            $controller = $container[MatchQueryController::class];
            return $controller->findMatchById($args['id']);
        });
        $app->get('/pitch', function () use ($container) {
            /** @var PitchQueryController $controller */
            $controller = $container[PitchQueryController::class];
            return $controller->findAllPitches();
        });
        $app->get('/pitch/{id}', function ($request, $response, $args) use ($container) {
            /** @var PitchQueryController $controller */
            $controller = $container[PitchQueryController::class];
            return $controller->findPitchById($args['id']);
        });
        $app->post('/season/{id}/start', function ($request, $response, $args) use ($container) {
            /** @var SeasonCommandController $controller */
            $controller = $container[SeasonCommandController::class];
            return $controller->start($args['id']);
        });
    }
}