<?php
/** @noinspection PhpUnusedParameterInspection */
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Routing;

use GraphQL\Type\Schema;
use HexagonalPlayground\Infrastructure\API\GraphQL\AppContext;
use HexagonalPlayground\Infrastructure\API\GraphQL\Controller as GraphQLController;
use HexagonalPlayground\Infrastructure\API\GraphQL\ErrorHandler;
use HexagonalPlayground\Infrastructure\API\Security\AuthenticationMiddleware;
use HexagonalPlayground\Infrastructure\API\Security\WebAuthn\AuthController;
use HexagonalPlayground\Infrastructure\API\Security\WebAuthn\CredentialController;
use HexagonalPlayground\Infrastructure\API\Security\WebAuthn\TestClientController;
use Slim\App;

class RouteProvider
{
    public function registerRoutes(App $app)
    {
        $app->group('/api', function() use ($app) {
            $container = $app->getContainer();
            $auth      = new AuthenticationMiddleware($container);

            $app->get('/webauthn/test', function () use ($container) {
                /** @var TestClientController $testClientController */
                $testClientController = $container->get(TestClientController::class);

                return $testClientController->show();
            });

            $app->post('/webauthn/credential/options', function ($request) use ($container) {
                /** @var CredentialController $credentialController */
                $credentialController = $container->get(CredentialController::class);

                return $credentialController->options($request);
            })->add($auth);

            $app->post('/webauthn/credential', function ($request) use ($container) {
                /** @var CredentialController $credentialController */
                $credentialController = $container->get(CredentialController::class);

                return $credentialController->create($request);
            })->add($auth);

            $app->get('/webauthn/credential', function ($request) use ($container) {
                /** @var CredentialController $credentialController */
                $credentialController = $container->get(CredentialController::class);

                return $credentialController->findAll($request);
            })->add($auth);

            $app->delete('/webauthn/credential', function ($request) use ($container) {
                /** @var CredentialController $credentialController */
                $credentialController = $container->get(CredentialController::class);

                return $credentialController->deleteAll($request);
            })->add($auth);

            $app->delete('/webauthn/credential/{id}', function ($request, $response, $args) use ($container) {
                /** @var CredentialController $credentialController */
                $credentialController = $container->get(CredentialController::class);

                return $credentialController->deleteOne($request, $args['id']);
            })->add($auth);

            $app->post('/webauthn/login/options', function ($request) use ($container) {
                /** @var AuthController $authController */
                $authController = $container->get(AuthController::class);

                return $authController->options($request);
            });

            $app->post('/webauthn/login', function ($request) use ($container) {
                /** @var AuthController $authController */
                $authController = $container->get(AuthController::class);

                return $authController->login($request);
            });

            $app->post('/graphql', function ($request, $response, $args) use ($container) {
                $appContext = new AppContext($request, $container);
                $errorHandler = new ErrorHandler($container->get('logger'), $appContext);
                $controller = new GraphQLController($appContext, $container->get(Schema::class), $errorHandler);

                return $controller->query($request);
            })->add($auth);
        });
    }
}
