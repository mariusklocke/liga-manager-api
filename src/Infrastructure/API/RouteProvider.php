<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API;

use HexagonalPlayground\Infrastructure\API\Controller\MatchCommandController;
use HexagonalPlayground\Infrastructure\API\Controller\MatchQueryController;
use HexagonalPlayground\Infrastructure\API\Controller\PitchQueryController;
use HexagonalPlayground\Infrastructure\API\Controller\SeasonCommandController;
use HexagonalPlayground\Infrastructure\API\Controller\SeasonQueryController;
use HexagonalPlayground\Infrastructure\API\Controller\TeamCommandController;
use HexagonalPlayground\Infrastructure\API\Controller\TeamQueryController;
use HexagonalPlayground\Infrastructure\API\Controller\TournamentCommandController;
use HexagonalPlayground\Infrastructure\API\Controller\TournamentQueryController;
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
        })->setName('findAllTeams');

        $app->get('/team/{id}', function ($request, $response, $args) use ($container) {
            /** @var TeamQueryController $controller */
            $controller = $container[TeamQueryController::class];
            return $controller->findTeamById($args['id']);
        })->setName('findTeamById');

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
        })->setName('findSeasonById');

        $app->get('/season/{id}/ranking', function ($request, $response, $args) use ($container) {
            /** @var SeasonQueryController $controller */
            $controller = $container[SeasonQueryController::class];
            return $controller->findRanking($args['id']);
        });

        $app->get('/season/{seasonId}/matches', function ($request, $response, $args) use ($container) {
            /** @var MatchQueryController $controller */
            $controller = $container[MatchQueryController::class];
            return $controller->findMatchesInSeason($args['seasonId'], $request);
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
        })->setName('findPitchById');

        $app->post('/season/{id}/start', function ($request, $response, $args) use ($container) {
            /** @var SeasonCommandController $controller */
            $controller = $container[SeasonCommandController::class];
            return $controller->start($args['id']);
        });

        $app->delete('/season/{id}', function ($request, $response, $args) use ($container) {
            /** @var SeasonCommandController $controller */
            $controller = $container[SeasonCommandController::class];
            return $controller->delete($args['id']);
        });

        $app->post('/season/{id}/matches', function ($request, $response, $args) use ($container) {
            /** @var SeasonCommandController $controller */
            $controller = $container[SeasonCommandController::class];
            return $controller->createMatches($args['id']);
        });

        $app->post('/match/{id}/kickoff', function ($request, $response, $args) use ($container) {
            /** @var MatchCommandController $controller */
            $controller = $container[MatchCommandController::class];
            return $controller->schedule($args['id'], $request);
        });

        $app->post('/match/{id}/location', function ($request, $response, $args) use ($container) {
            /** @var MatchCommandController $controller */
            $controller = $container[MatchCommandController::class];
            return $controller->locate($args['id'], $request);
        });

        $app->post('/match/{id}/result', function ($request, $response, $args) use ($container) {
            /** @var MatchCommandController $controller */
            $controller = $container[MatchCommandController::class];
            return $controller->submitResult($args['id'], $request);
        });

        $app->post('/match/{id}/cancellation', function ($request, $response, $args) use ($container) {
            /** @var MatchCommandController $controller */
            $controller = $container[MatchCommandController::class];
            return $controller->cancel($args['id']);
        });

        $app->put('/season/{seasonId}/team/{teamId}', function ($request, $response, $args) use ($container) {
            /** @var SeasonCommandController $controller */
            $controller = $container[SeasonCommandController::class];
            return $controller->addTeam($args['seasonId'], $args['teamId']);
        });

        $app->delete('/season/{seasonId}/team/{teamId}', function ($request, $response, $args) use ($container) {
            /** @var SeasonCommandController $controller */
            $controller = $container[SeasonCommandController::class];
            return $controller->removeTeam($args['seasonId'], $args['teamId']);
        });

        $app->post('/season', function ($request) use ($container) {
            /** @var SeasonCommandController $controller */
            $controller = $container[SeasonCommandController::class];
            return $controller->createSeason($request);
        });

        $app->post('/tournament', function ($request) use ($container) {
            /** @var TournamentCommandController $controller */
            $controller = $container[TournamentCommandController::class];
            return $controller->create($request);
        });

        $app->put('/tournament/{id}/round/{round:[0-9]+}', function ($request, $response, $args) use ($container) {
            /** @var TournamentCommandController $controller */
            $controller = $container[TournamentCommandController::class];
            return $controller->setRound($args['id'], (int) $args['round'], $request);
        });

        $app->get('/tournament', function () use ($container) {
            /** @var TournamentQueryController $controller */
            $controller = $container[TournamentQueryController::class];
            return $controller->findAllTournaments();
        });

        $app->get('/tournament/{id}', function ($request, $response, $args) use ($container) {
            /** @var TournamentQueryController $controller */
            $controller = $container[TournamentQueryController::class];
            return $controller->findTournamentById($args['id']);
        });

        $app->get('/tournament/{id}/matches', function ($request, $response, $args) use ($container) {
            /** @var MatchQueryController $controller */
            $controller = $container[MatchQueryController::class];
            return $controller->findMatchesInTournament($args['id']);
        });
    }
}