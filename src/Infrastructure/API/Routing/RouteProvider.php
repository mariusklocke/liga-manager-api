<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Routing;

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
use HexagonalPlayground\Infrastructure\API\Security\AuthenticationMiddleware;
use HexagonalPlayground\Infrastructure\Persistence\Read\MatchRepository;
use HexagonalPlayground\Infrastructure\Persistence\Read\PitchRepository;
use HexagonalPlayground\Infrastructure\Persistence\Read\RankingRepository;
use HexagonalPlayground\Infrastructure\Persistence\Read\SeasonRepository;
use HexagonalPlayground\Infrastructure\Persistence\Read\TeamRepository;
use HexagonalPlayground\Infrastructure\Persistence\Read\TournamentRepository;
use Slim\App;

class RouteProvider
{
    public function registerRoutes(App $app)
    {
        $app->group('/api', function() use ($app) {
            $container = $app->getContainer();
            $anyAuth   = new AuthenticationMiddleware($container);
            $basicAuth = new AuthenticationMiddleware($container, true);

            $app->get('/team', function () use ($container) {
                return (new TeamQueryController($container[TeamRepository::class]))->findAllTeams();
            });

            $app->get('/team/{id}', function ($request, $response, $args) use ($container) {
                return (new TeamQueryController($container[TeamRepository::class]))->findTeamById($args['id']);
            });

            $app->put('/team/{id}/contact', function ($request, $response, $args) use ($container) {
                return (new TeamCommandController($container['commandBus']))->updateContact($args['id'], $request);
            })->add($anyAuth);

            $app->get('/season/{id}/team', function ($request, $response, $args) use ($container) {
                return (new TeamQueryController($container[TeamRepository::class]))->findTeamsBySeasonId($args['id']);
            });

            $app->post('/team', function ($request) use ($container) {
                return (new TeamCommandController($container['commandBus']))->create($request);
            })->add($anyAuth);

            $app->delete('/team/{id}', function ($request, $response, $args) use ($container) {
                return (new TeamCommandController($container['commandBus']))->delete($args['id']);
            })->add($anyAuth);

            $app->get('/season', function () use ($container) {
                return (
                    new SeasonQueryController(
                        $container[SeasonRepository::class],
                        $container[RankingRepository::class],
                        $container[MatchRepository::class]
                    )
                )->findAllSeasons();
            });

            $app->get('/season/{id}', function ($request, $response, $args) use ($container) {
                return (
                    new SeasonQueryController(
                        $container[SeasonRepository::class],
                        $container[RankingRepository::class],
                        $container[MatchRepository::class]
                    )
                )->findSeasonById($args['id']);
            });

            $app->get('/season/{id}/ranking', function ($request, $response, $args) use ($container) {
                return (
                    new SeasonQueryController(
                        $container[SeasonRepository::class],
                        $container[RankingRepository::class],
                        $container[MatchRepository::class]
                    )
                )->findRanking($args['id']);
            });

            $app->get('/season/{id}/matches', function ($request, $response, $args) use ($container) {
                return (new MatchQueryController($container[MatchRepository::class]))
                    ->findMatchesInSeason($args['id'], $request);
            });

            $app->get('/match/{id}', function ($request, $response, $args) use ($container) {
                return (new MatchQueryController($container[MatchRepository::class]))->findMatchById($args['id']);
            });

            $app->get('/pitch', function () use ($container) {
                return (new PitchQueryController($container[PitchRepository::class]))->findAllPitches();
            });

            $app->get('/pitch/{id}', function ($request, $response, $args) use ($container) {
                return (new PitchQueryController($container[PitchRepository::class]))->findPitchById($args['id']);
            });

            $app->post('/pitch', function ($request) use ($container) {
                return (new PitchCommandController($container['commandBus']))->create($request);
            })->add($anyAuth);

            $app->put('/pitch/{id}/contact', function ($request, $response, $args) use ($container) {
                return (new PitchCommandController($container['commandBus']))->updateContact($args['id'], $request);
            })->add($anyAuth);

            $app->post('/season/{id}/start', function ($request, $response, $args) use ($container) {
                return (new SeasonCommandController($container['commandBus']))->start($args['id']);
            })->add($anyAuth);

            $app->delete('/season/{id}', function ($request, $response, $args) use ($container) {
                return (new SeasonCommandController($container['commandBus']))->delete($args['id']);
            })->add($anyAuth);

            $app->post('/season/{id}/matches', function ($request, $response, $args) use ($container) {
                return (new SeasonCommandController($container['commandBus']))->createMatches($args['id'], $request);
            })->add($anyAuth);

            $app->post('/match/{id}/kickoff', function ($request, $response, $args) use ($container) {
                return (new MatchCommandController($container['commandBus']))->schedule($args['id'], $request);
            })->add($anyAuth);

            $app->post('/match/{id}/location', function ($request, $response, $args) use ($container) {
                return (new MatchCommandController($container['commandBus']))->locate($args['id'], $request);
            })->add($anyAuth);

            $app->post('/match/{id}/result', function ($request, $response, $args) use ($container) {
                return (new MatchCommandController($container['commandBus']))->submitResult($args['id'], $request);
            })->add($anyAuth);

            $app->post('/match/{id}/cancellation', function ($request, $response, $args) use ($container) {
                return (new MatchCommandController($container['commandBus']))->cancel($args['id']);
            })->add($anyAuth);

            $app->put('/season/{season_id}/team/{team_id}', function ($request, $response, $args) use ($container) {
                return (new SeasonCommandController($container['commandBus']))->addTeam($args['season_id'], $args['team_id']);
            })->add($anyAuth);

            $app->delete('/season/{season_id}/team/{team_id}', function ($request, $response, $args) use ($container) {
                return (new SeasonCommandController($container['commandBus']))->removeTeam($args['season_id'], $args['team_id']);
            })->add($anyAuth);

            $app->post('/season', function ($request) use ($container) {
                return (new SeasonCommandController($container['commandBus']))->createSeason($request);
            })->add($anyAuth);

            $app->post('/tournament', function ($request) use ($container) {
                return (new TournamentCommandController($container['commandBus']))->create($request);
            })->add($anyAuth);

            $app->put('/tournament/{id}/round/{round}', function ($request, $response, $args) use ($container) {
                return (new TournamentCommandController($container['commandBus']))->setRound($args['id'], (int) $args['round'], $request);
            })->add($anyAuth);

            $app->get('/tournament', function () use ($container) {
                return (new TournamentQueryController($container[TournamentRepository::class]))->findAllTournaments();
            });

            $app->get('/tournament/{id}', function ($request, $response, $args) use ($container) {
                return (new TournamentQueryController($container[TournamentRepository::class]))->findTournamentById($args['id']);
            });

            $app->get('/tournament/{id}/matches', function ($request, $response, $args) use ($container) {
                return (new MatchQueryController($container[MatchRepository::class]))->findMatchesInTournament($args['id']);
            });

            $app->get('/user/me', function ($request) use ($container) {
                return (new UserQueryController())->getAuthenticatedUser($request);
            })->add($anyAuth);

            $app->put('/user/me/password', function ($request) use ($container) {
                return (new UserCommandController($container['commandBus']))->changePassword($request);
            })->add($basicAuth);

            $app->post('/user', function ($request) use ($container) {
                return (new UserCommandController($container['commandBus']))->createUser($request);
            })->add($anyAuth);

            $app->post('/user/me/password/reset', function ($request) use ($container) {
                return (new UserCommandController($container['commandBus']))->sendPasswordResetMail($request);
            });
        });
    }
}