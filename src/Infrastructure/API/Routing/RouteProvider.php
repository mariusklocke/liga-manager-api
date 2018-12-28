<?php
/** @noinspection PhpUnusedParameterInspection */
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Routing;

use HexagonalPlayground\Infrastructure\API\Controller\EventQueryController;
use HexagonalPlayground\Infrastructure\API\Controller\MatchCommandController;
use HexagonalPlayground\Infrastructure\API\Controller\MatchDayCommandController;
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
use HexagonalPlayground\Infrastructure\API\Security\AuthenticationMiddleware;
use HexagonalPlayground\Infrastructure\Persistence\Read\EventRepository;
use HexagonalPlayground\Infrastructure\Persistence\Read\MatchRepository;
use HexagonalPlayground\Infrastructure\Persistence\Read\PitchRepository;
use HexagonalPlayground\Infrastructure\Persistence\Read\SeasonRepository;
use HexagonalPlayground\Infrastructure\Persistence\Read\TeamRepository;
use HexagonalPlayground\Infrastructure\Persistence\Read\TournamentRepository;
use HexagonalPlayground\Infrastructure\Persistence\Read\UserRepository;
use Slim\App;

class RouteProvider
{
    public function registerRoutes(App $app)
    {
        $app->group('/api', function() use ($app) {
            $container = $app->getContainer();
            $auth      = new AuthenticationMiddleware($container);

            $app->get('/teams', function () use ($container) {
                return (new TeamQueryController($container[TeamRepository::class]))->findAllTeams();
            });

            $app->get('/teams/{id}', function ($request, $response, $args) use ($container) {
                return (new TeamQueryController($container[TeamRepository::class]))->findTeamById($args['id']);
            });

            $app->put('/teams/{id}/contact', function ($request, $response, $args) use ($container) {
                return (new TeamCommandController($container['commandBus']))->updateContact($args['id'], $request);
            })->add($auth);

            $app->put('/teams/{id}/name', function ($request, $response, $args) use ($container) {
                return (new TeamCommandController($container['commandBus']))->rename($args['id'], $request);
            })->add($auth);

            $app->get('/seasons/{id}/teams', function ($request, $response, $args) use ($container) {
                return (new TeamQueryController($container[TeamRepository::class]))->findTeamsBySeasonId($args['id']);
            });

            $app->post('/teams', function ($request) use ($container) {
                return (new TeamCommandController($container['commandBus']))->create($request);
            })->add($auth);

            $app->delete('/teams/{id}', function ($request, $response, $args) use ($container) {
                return (new TeamCommandController($container['commandBus']))->delete($request, $args['id']);
            })->add($auth);

            $app->get('/seasons', function () use ($container) {
                return (new SeasonQueryController($container[SeasonRepository::class]))->findAllSeasons();
            });

            $app->get('/seasons/{id}', function ($request, $response, $args) use ($container) {
                return (new SeasonQueryController($container[SeasonRepository::class]))->findSeasonById($args['id']);
            });

            $app->get('/seasons/{id}/ranking', function ($request, $response, $args) use ($container) {
                return (new SeasonQueryController($container[SeasonRepository::class]))->findRanking($args['id']);
            });

            $app->post('/seasons/{season_id}/ranking/penalties', function ($request, $response, $args) use ($container) {
                return (new SeasonCommandController($container['commandBus']))
                    ->addRankingPenalty($request, $args['season_id']);
            })->add($auth);

            $app->delete('/seasons/{season_id}/ranking/penalties/{penalty_id}', function ($request, $response, $args) use ($container) {
                return (new SeasonCommandController($container['commandBus']))
                    ->removeRankingPenalty($request, $args['season_id'], $args['penalty_id']);
            })->add($auth);

            $app->get('/matches', function ($request) use ($container) {
                return (new MatchQueryController($container[MatchRepository::class]))->findMatches($request);
            });

            $app->get('/matches/{id}', function ($request, $response, $args) use ($container) {
                return (new MatchQueryController($container[MatchRepository::class]))->findMatchById($args['id']);
            });

            $app->get('/pitches', function () use ($container) {
                return (new PitchQueryController($container[PitchRepository::class]))->findAllPitches();
            });

            $app->get('/pitches/{id}', function ($request, $response, $args) use ($container) {
                return (new PitchQueryController($container[PitchRepository::class]))->findPitchById($args['id']);
            });

            $app->post('/pitches', function ($request) use ($container) {
                return (new PitchCommandController($container['commandBus']))->create($request);
            })->add($auth);

            $app->put('/pitches/{id}/contact', function ($request, $response, $args) use ($container) {
                return (new PitchCommandController($container['commandBus']))->updateContact($args['id'], $request);
            })->add($auth);

            $app->post('/seasons/{id}/start', function ($request, $response, $args) use ($container) {
                return (new SeasonCommandController($container['commandBus']))->start($request, $args['id']);
            })->add($auth);

            $app->post('/seasons/{id}/end', function ($request, $response, $args) use ($container) {
                return (new SeasonCommandController($container['commandBus']))->end($request, $args['id']);
            })->add($auth);

            $app->delete('/seasons/{id}', function ($request, $response, $args) use ($container) {
                return (new SeasonCommandController($container['commandBus']))->delete($request, $args['id']);
            })->add($auth);

            $app->get('/seasons/{id}/match_days', function ($request, $response, $args) use ($container) {
                return (new SeasonQueryController($container[SeasonRepository::class]))->findMatchDays($args['id']);
            });

            $app->post('/seasons/{id}/match_days', function ($request, $response, $args) use ($container) {
                return (new SeasonCommandController($container['commandBus']))->createMatches($args['id'], $request);
            })->add($auth);

            $app->post('/matches/{id}/kickoff', function ($request, $response, $args) use ($container) {
                return (new MatchCommandController($container['commandBus']))->schedule($args['id'], $request);
            })->add($auth);

            $app->post('/matches/{id}/location', function ($request, $response, $args) use ($container) {
                return (new MatchCommandController($container['commandBus']))->locate($args['id'], $request);
            })->add($auth);

            $app->post('/matches/{id}/result', function ($request, $response, $args) use ($container) {
                return (new MatchCommandController($container['commandBus']))->submitResult($args['id'], $request);
            })->add($auth);

            $app->post('/matches/{id}/cancellation', function ($request, $response, $args) use ($container) {
                return (new MatchCommandController($container['commandBus']))->cancel($request, $args['id']);
            })->add($auth);

            $app->put('/seasons/{season_id}/teams/{team_id}', function ($request, $response, $args) use ($container) {
                return (new SeasonCommandController($container['commandBus']))->addTeam($request, $args['season_id'], $args['team_id']);
            })->add($auth);

            $app->delete('/seasons/{season_id}/teams/{team_id}', function ($request, $response, $args) use ($container) {
                return (new SeasonCommandController($container['commandBus']))->removeTeam($request, $args['season_id'], $args['team_id']);
            })->add($auth);

            $app->post('/seasons', function ($request) use ($container) {
                return (new SeasonCommandController($container['commandBus']))->createSeason($request);
            })->add($auth);

            $app->post('/tournaments', function ($request) use ($container) {
                return (new TournamentCommandController($container['commandBus']))->create($request);
            })->add($auth);

            $app->get('/tournaments/{id}/rounds', function ($request, $response, $args) use ($container) {
                return (new TournamentQueryController($container[TournamentRepository::class]))
                    ->findRounds($args['id']);
            });

            $app->put('/tournaments/{id}/rounds/{round}', function ($request, $response, $args) use ($container) {
                return (new TournamentCommandController($container['commandBus']))->setRound($args['id'], (int) $args['round'], $request);
            })->add($auth);

            $app->get('/tournaments', function () use ($container) {
                return (new TournamentQueryController($container[TournamentRepository::class]))->findAllTournaments();
            });

            $app->get('/tournaments/{id}', function ($request, $response, $args) use ($container) {
                return (new TournamentQueryController($container[TournamentRepository::class]))->findTournamentById($args['id']);
            });

            $app->delete('/tournaments/{id}', function ($request, $response, $args) use ($container) {
                return (new TournamentCommandController($container['commandBus']))->delete($request, $args['id']);
            })->add($auth);

            $app->get('/users/me', function ($request) use ($container) {
                return (new UserQueryController($container[UserRepository::class]))->getAuthenticatedUser($request);
            })->add($auth);

            $app->get('/users', function ($request) use ($container) {
                return (new UserQueryController($container[UserRepository::class]))->findAllUsers($request);
            })->add($auth);

            $app->put('/users/me/password', function ($request) use ($container) {
                return (new UserCommandController($container['commandBus']))->changePassword($request);
            })->add($auth);

            $app->post('/users', function ($request) use ($container) {
                return (new UserCommandController($container['commandBus']))->createUser($request);
            })->add($auth);

            $app->delete('/users/{id}', function ($request, $response, $args) use ($container) {
                return (new UserCommandController($container['commandBus']))->deleteUser($request, $args['id']);
            })->add($auth);

            $app->patch('/users/{id}', function ($request, $response, $args) use ($container) {
                return (new UserCommandController($container['commandBus']))->updateUser($args['id'], $request);
            })->add($auth);

            $app->post('/users/me/password/reset', function ($request) use ($container) {
                return (new UserCommandController($container['commandBus']))->sendPasswordResetMail($request);
            });

            $app->patch('/match_days/{id}', function ($request, $response, $args) use ($container) {
                return (new MatchDayCommandController($container['commandBus']))->rescheduleMatchDay(
                    $args['id'],
                    $request
                );
            })->add($auth);

            $app->get('/events', function ($request, $response, $args) use ($container) {
                return (new EventQueryController($container[EventRepository::class]))->findLatestEvents($request);
            });

            $app->get('/events/{id}', function ($request, $response, $args) use ($container) {
                return (new EventQueryController($container[EventRepository::class]))->findEventById($args['id']);
            });
        });
    }
}