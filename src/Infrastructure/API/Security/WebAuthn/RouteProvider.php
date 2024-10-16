<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Security\WebAuthn;

use HexagonalPlayground\Infrastructure\API\RouteProviderInterface;
use Slim\Interfaces\RouteCollectorProxyInterface;

class RouteProvider implements RouteProviderInterface
{
    public function register(RouteCollectorProxyInterface $routeCollectorProxy): void
    {
        $routeCollectorProxy->get('/webauthn/test', TestClientController::class);
        $routeCollectorProxy->post('/webauthn/credential/options', CredentialOptionsController::class);
        $routeCollectorProxy->map(['GET', 'POST'], '/webauthn/credential', CredentialController::class);
        $routeCollectorProxy->delete('/webauthn/credential/{id}', CredentialController::class);
        $routeCollectorProxy->post('/webauthn/login/options', LoginOptionsController::class);
        $routeCollectorProxy->post('/webauthn/login', LoginController::class);
    }
}
