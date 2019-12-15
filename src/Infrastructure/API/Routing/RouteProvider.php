<?php
/** @noinspection PhpUnusedParameterInspection */
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Routing;

use HexagonalPlayground\Infrastructure\API\GraphQL\Controller as GraphQLController;
use HexagonalPlayground\Infrastructure\API\Security\AuthenticationMiddleware;
use HexagonalPlayground\Infrastructure\API\Security\WebAuthn\AuthController;
use HexagonalPlayground\Infrastructure\API\Security\WebAuthn\CredentialController;
use HexagonalPlayground\Infrastructure\API\Security\WebAuthn\TestClientController;
use Psr\Container\ContainerInterface;
use Slim\Interfaces\RouteCollectorProxyInterface;

class RouteProvider
{
    public function registerRoutes(RouteCollectorProxyInterface $routeCollector, ContainerInterface $container)
    {
        $routeCollector->group('/api', function(RouteCollectorProxyInterface $group) use ($container) {
            $auth = new AuthenticationMiddleware($container);

            $group->get('/webauthn/test', function ($request, $response) use ($container) {
                /** @var TestClientController $testClientController */
                $testClientController = $container->get(TestClientController::class);

                return $testClientController->show($response);
            });

            $group->post('/webauthn/credential/options', function ($request, $response) use ($container) {
                /** @var CredentialController $credentialController */
                $credentialController = $container->get(CredentialController::class);

                return $credentialController->options($request, $response);
            })->add($auth);

            $group->post('/webauthn/credential', function ($request, $response) use ($container) {
                /** @var CredentialController $credentialController */
                $credentialController = $container->get(CredentialController::class);

                return $credentialController->create($request, $response);
            })->add($auth);

            $group->get('/webauthn/credential', function ($request, $response) use ($container) {
                /** @var CredentialController $credentialController */
                $credentialController = $container->get(CredentialController::class);

                return $credentialController->findAll($request, $response);
            })->add($auth);

            $group->delete('/webauthn/credential', function ($request, $response) use ($container) {
                /** @var CredentialController $credentialController */
                $credentialController = $container->get(CredentialController::class);

                return $credentialController->deleteAll($request, $response);
            })->add($auth);

            $group->delete('/webauthn/credential/{id}', function ($request, $response, $args) use ($container) {
                /** @var CredentialController $credentialController */
                $credentialController = $container->get(CredentialController::class);

                return $credentialController->deleteOne($request, $response, $args['id']);
            })->add($auth);

            $group->post('/webauthn/login/options', function ($request, $response) use ($container) {
                /** @var AuthController $authController */
                $authController = $container->get(AuthController::class);

                return $authController->options($request, $response);
            });

            $group->post('/webauthn/login', function ($request, $response) use ($container) {
                /** @var AuthController $authController */
                $authController = $container->get(AuthController::class);

                return $authController->login($request, $response);
            });

            $group->post('/graphql', function ($request, $response, $args) use ($container) {
                $controller = new GraphQLController($container);

                return $controller->query($request, $response);
            })->add($auth);
        });
    }
}
