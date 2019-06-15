<?php
/** @noinspection PhpUnusedParameterInspection */
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Routing;

use HexagonalPlayground\Infrastructure\API\GraphQL\Controller as GraphQLController;
use HexagonalPlayground\Infrastructure\API\Security\AuthenticationMiddleware;
use Slim\App;
use Slim\Http\Response;
use Slim\Http\StatusCode;

class RouteProvider
{
    public function registerRoutes(App $app)
    {
        $app->group('/api', function() use ($app) {
            $container = $app->getContainer();
            $auth      = new AuthenticationMiddleware($container);

            $app->get('/teams', function () use ($container) {
                return new Response(StatusCode::HTTP_GONE);
            });

            $app->get('/teams/{id}', function ($request, $response, $args) use ($container) {
                return new Response(StatusCode::HTTP_GONE);
            });

            $app->put('/teams/{id}/contact', function ($request, $response, $args) use ($container) {
                return new Response(StatusCode::HTTP_GONE);
            })->add($auth);

            $app->put('/teams/{id}/name', function ($request, $response, $args) use ($container) {
                return new Response(StatusCode::HTTP_GONE);
            })->add($auth);

            $app->get('/seasons/{id}/teams', function ($request, $response, $args) use ($container) {
                return new Response(StatusCode::HTTP_GONE);
            });

            $app->post('/teams', function ($request) use ($container) {
                return new Response(StatusCode::HTTP_GONE);
            })->add($auth);

            $app->delete('/teams/{id}', function ($request, $response, $args) use ($container) {
                return new Response(StatusCode::HTTP_GONE);
            })->add($auth);

            $app->get('/seasons', function () use ($container) {
                return new Response(StatusCode::HTTP_GONE);
            });

            $app->get('/seasons/{id}', function ($request, $response, $args) use ($container) {
                return new Response(StatusCode::HTTP_GONE);
            });

            $app->get('/seasons/{id}/ranking', function ($request, $response, $args) use ($container) {
                return new Response(StatusCode::HTTP_GONE);
            });

            $app->post('/seasons/{season_id}/ranking/penalties', function ($request, $response, $args) use ($container) {
                return new Response(StatusCode::HTTP_GONE);
            })->add($auth);

            $app->delete('/seasons/{season_id}/ranking/penalties/{penalty_id}', function ($request, $response, $args) use ($container) {
                return new Response(StatusCode::HTTP_GONE);
            })->add($auth);

            $app->get('/matches', function ($request) use ($container) {
                return new Response(StatusCode::HTTP_GONE);
            });

            $app->get('/matches/{id}', function ($request, $response, $args) use ($container) {
                return new Response(StatusCode::HTTP_GONE);
            });

            $app->get('/pitches', function () use ($container) {
                return new Response(StatusCode::HTTP_GONE);
            });

            $app->get('/pitches/{id}', function ($request, $response, $args) use ($container) {
                return new Response(StatusCode::HTTP_GONE);
            });

            $app->post('/pitches', function ($request) use ($container) {
                return new Response(StatusCode::HTTP_GONE);
            })->add($auth);

            $app->put('/pitches/{id}/contact', function ($request, $response, $args) use ($container) {
                return new Response(StatusCode::HTTP_GONE);
            })->add($auth);

            $app->post('/seasons/{id}/start', function ($request, $response, $args) use ($container) {
                return new Response(StatusCode::HTTP_GONE);
            })->add($auth);

            $app->post('/seasons/{id}/end', function ($request, $response, $args) use ($container) {
                return new Response(StatusCode::HTTP_GONE);
            })->add($auth);

            $app->delete('/seasons/{id}', function ($request, $response, $args) use ($container) {
                return new Response(StatusCode::HTTP_GONE);
            })->add($auth);

            $app->get('/seasons/{id}/match_days', function ($request, $response, $args) use ($container) {
                return new Response(StatusCode::HTTP_GONE);
            });

            $app->post('/seasons/{id}/match_days', function ($request, $response, $args) use ($container) {
                return new Response(StatusCode::HTTP_GONE);
            })->add($auth);

            $app->post('/matches/{id}/kickoff', function ($request, $response, $args) use ($container) {
                return new Response(StatusCode::HTTP_GONE);
            })->add($auth);

            $app->post('/matches/{id}/location', function ($request, $response, $args) use ($container) {
                return new Response(StatusCode::HTTP_GONE);
            })->add($auth);

            $app->post('/matches/{id}/result', function ($request, $response, $args) use ($container) {
                return new Response(StatusCode::HTTP_GONE);
            })->add($auth);

            $app->post('/matches/{id}/cancellation', function ($request, $response, $args) use ($container) {
                return new Response(StatusCode::HTTP_GONE);
            })->add($auth);

            $app->put('/seasons/{season_id}/teams/{team_id}', function ($request, $response, $args) use ($container) {
                return new Response(StatusCode::HTTP_GONE);
            })->add($auth);

            $app->delete('/seasons/{season_id}/teams/{team_id}', function ($request, $response, $args) use ($container) {
                return new Response(StatusCode::HTTP_GONE);
            })->add($auth);

            $app->post('/seasons', function ($request) use ($container) {
                return new Response(StatusCode::HTTP_GONE);
            })->add($auth);

            $app->post('/tournaments', function ($request) use ($container) {
                return new Response(StatusCode::HTTP_GONE);
            })->add($auth);

            $app->get('/tournaments/{id}/rounds', function ($request, $response, $args) use ($container) {
                return new Response(StatusCode::HTTP_GONE);
            });

            $app->put('/tournaments/{id}/rounds/{round}', function ($request, $response, $args) use ($container) {
                return new Response(StatusCode::HTTP_GONE);
            })->add($auth);

            $app->get('/tournaments', function () use ($container) {
                return new Response(StatusCode::HTTP_GONE);
            });

            $app->get('/tournaments/{id}', function ($request, $response, $args) use ($container) {
                return new Response(StatusCode::HTTP_GONE);
            });

            $app->delete('/tournaments/{id}', function ($request, $response, $args) use ($container) {
                return new Response(StatusCode::HTTP_GONE);
            })->add($auth);

            $app->get('/users/me', function ($request) use ($container) {
                return new Response(StatusCode::HTTP_GONE);
            })->add($auth);

            $app->get('/users', function ($request) use ($container) {
                return new Response(StatusCode::HTTP_GONE);
            })->add($auth);

            $app->put('/users/me/password', function ($request) use ($container) {
                return new Response(StatusCode::HTTP_GONE);
            })->add($auth);

            $app->post('/users', function ($request) use ($container) {
                return new Response(StatusCode::HTTP_GONE);
            })->add($auth);

            $app->delete('/users/{id}', function ($request, $response, $args) use ($container) {
                return new Response(StatusCode::HTTP_GONE);
            })->add($auth);

            $app->patch('/users/{id}', function ($request, $response, $args) use ($container) {
                return new Response(StatusCode::HTTP_GONE);
            })->add($auth);

            $app->post('/users/me/password/reset', function ($request) use ($container) {
                return new Response(StatusCode::HTTP_GONE);
            });

            $app->patch('/match_days/{id}', function ($request, $response, $args) use ($container) {
                return new Response(StatusCode::HTTP_GONE);
            })->add($auth);

            $app->get('/events', function ($request, $response, $args) use ($container) {
                return new Response(StatusCode::HTTP_GONE);
            });

            $app->get('/events/{id}', function ($request, $response, $args) use ($container) {
                return new Response(StatusCode::HTTP_GONE);
            });

            $app->post('/graphql', function ($request, $response, $args) use ($container) {
                return (new GraphQLController())->query($request, $container);
            })->add($auth);
        });
    }
}