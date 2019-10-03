<?php
/** @noinspection PhpUnusedParameterInspection */
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Routing;

use HexagonalPlayground\Infrastructure\API\GraphQL\Controller as GraphQLController;
use HexagonalPlayground\Infrastructure\API\Security\AuthenticationMiddleware;
use HexagonalPlayground\Infrastructure\API\Security\WebAuthn\AuthController;
use HexagonalPlayground\Infrastructure\API\Security\WebAuthn\CredentialController;
use Slim\App;

class RouteProvider
{
    public function registerRoutes(App $app)
    {
        $app->group('/api', function() use ($app) {
            $container = $app->getContainer();
            $auth      = new AuthenticationMiddleware($container);

            $app->post('/webauthn/credential/options', function ($request) use ($container) {
                /** @var CredentialController $credentialController */
                $credentialController = $container[CredentialController::class];

                return $credentialController->options($request);
            })->add($auth);

            $app->post('/webauthn/credential', function ($request) use ($container) {
                /** @var CredentialController $credentialController */
                $credentialController = $container[CredentialController::class];

                return $credentialController->create($request);
            })->add($auth);

            $app->post('/webauthn/login/options', function ($request) use ($container) {
                /** @var AuthController $authController */
                $authController = $container[AuthController::class];

                return $authController->options($request);
            });

            $app->post('/webauthn/login', function ($request) use ($container) {
                /** @var AuthController $authController */
                $authController = $container[AuthController::class];

                return $authController->login($request);
            });

            $app->post('/graphql', function ($request, $response, $args) use ($container) {
                return (new GraphQLController())->query($request, $container);
            })->add($auth);
        });
    }
}
