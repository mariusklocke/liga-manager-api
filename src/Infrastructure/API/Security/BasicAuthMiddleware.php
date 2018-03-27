<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Security;

use HexagonalPlayground\Infrastructure\API\Exception\BadRequestException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class BasicAuthMiddleware extends AuthenticationMiddleware
{
    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next)
    {
        list($type, $secret) = $this->parseAuthHeader($request);
        if (!is_string($type) || 'basic' !== strtolower($type)) {
            return $next($request, $response);
        }

        list($email, $password) = $this->parseCredentials($secret);
        $this->getAuthenticator()->authenticateByCredentials($email, $password);

        /** @var ResponseInterface $response */
        $response = $next($request, $response);

        /**
         * Creating the token after the controller is important when changing a user password
         * In this case the token has to be created *AFTER* the password has been changed, because otherwise
         * it would be considered invalid for the next request
         *
         * @see Authenticator::authenticateByToken()
         */

        /** @var JsonWebToken $token */
        $token = $this->getAuthenticator()->getAuthenticatedToken();
        return $response->withHeader('X-Token', $token->encode());
    }

    /**
     * @param string $encoded
     * @return array
     */
    private function parseCredentials(string $encoded): array
    {
        list($email, $password) = explode(':', base64_decode($encoded), 2);
        if (!is_string($email) || !is_string($password)) {
            throw new BadRequestException('Malformed BasicAuth credentials');
        }

        return [$email, $password];
    }
}