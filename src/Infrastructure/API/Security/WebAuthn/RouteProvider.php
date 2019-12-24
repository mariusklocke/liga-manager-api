<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Security\WebAuthn;

use HexagonalPlayground\Infrastructure\API\RouteProviderInterface;
use Slim\Interfaces\RouteCollectorProxyInterface;

class RouteProvider implements RouteProviderInterface
{
    public function register(RouteCollectorProxyInterface $collector): void
    {
        $collector->get('/webauthn/test', TestClientController::class . ':show');
        $collector->post('/webauthn/credential/options', CredentialController::class . ':options');
        $collector->post('/webauthn/credential', CredentialController::class . ':create');
        $collector->get('/webauthn/credential', CredentialController::class . ':findAll');
        $collector->delete('/webauthn/credential', CredentialController::class . ':deleteAll');
        $collector->delete('/webauthn/credential/{id}', CredentialController::class . ':deleteOne');
        $collector->post('/webauthn/login/options', AuthController::class . ':options');
        $collector->post('/webauthn/login', AuthController::class . ':login');
    }
}