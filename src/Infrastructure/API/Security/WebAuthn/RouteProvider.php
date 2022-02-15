<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Security\WebAuthn;

use HexagonalPlayground\Infrastructure\API\RouteProviderInterface;
use HexagonalPlayground\Infrastructure\API\Security\WebAuthn\Action\DeleteCredentialAction;
use HexagonalPlayground\Infrastructure\API\Security\WebAuthn\Action\GetLoginOptionsAction;
use HexagonalPlayground\Infrastructure\API\Security\WebAuthn\Action\GetRegisterOptionsAction;
use HexagonalPlayground\Infrastructure\API\Security\WebAuthn\Action\GetTestClientAction;
use HexagonalPlayground\Infrastructure\API\Security\WebAuthn\Action\ListCredentialsAction;
use HexagonalPlayground\Infrastructure\API\Security\WebAuthn\Action\PerformLoginAction;
use HexagonalPlayground\Infrastructure\API\Security\WebAuthn\Action\RegisterCredentialAction;
use Slim\Interfaces\RouteCollectorProxyInterface;

class RouteProvider implements RouteProviderInterface
{
    public function register(RouteCollectorProxyInterface $routeCollectorProxy): void
    {
        $routeCollectorProxy->get('/webauthn/test', GetTestClientAction::class);
        $routeCollectorProxy->post('/webauthn/credential/options', GetRegisterOptionsAction::class);
        $routeCollectorProxy->post('/webauthn/credential', RegisterCredentialAction::class);
        $routeCollectorProxy->get('/webauthn/credential', ListCredentialsAction::class);
        $routeCollectorProxy->delete('/webauthn/credential/{id}', DeleteCredentialAction::class);
        $routeCollectorProxy->post('/webauthn/login/options', GetLoginOptionsAction::class);
        $routeCollectorProxy->post('/webauthn/login', PerformLoginAction::class);
    }
}
