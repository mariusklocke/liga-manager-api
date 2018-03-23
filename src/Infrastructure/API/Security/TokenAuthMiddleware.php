<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Security;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class TokenAuthMiddleware extends AuthenticationMiddleware
{
    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next)
    {
        list($type, $secret) = $this->parseAuthHeader($request);
        if (is_string($type) && 'bearer' === strtolower($type)) {
            $this->getAuthenticator()->authenticateByToken(JsonWebToken::decode($secret));
        }
        return $next($request, $response);
    }
}