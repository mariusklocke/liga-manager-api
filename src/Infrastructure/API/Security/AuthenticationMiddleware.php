<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Security;

use DateTimeImmutable;
use HexagonalPlayground\Application\Exception\AuthenticationException;
use HexagonalPlayground\Application\Security\TokenFactoryInterface;
use HexagonalPlayground\Domain\User;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AuthenticationMiddleware
{
    /** @var ContainerInterface */
    private $container;

    /** @var bool */
    private $credentialsRequired;

    /**
     * @param ContainerInterface $container
     * @param bool $credentialsRequired
     */
    public function __construct(ContainerInterface $container, bool $credentialsRequired = false)
    {
        $this->container = $container;
        $this->credentialsRequired = $credentialsRequired;
    }

    /**
     * @return Authenticator
     */
    private function getAuthenticator(): Authenticator
    {
        return $this->container->get(Authenticator::class);
    }

    /**
     * @return TokenFactoryInterface
     */
    private function getTokenFactory(): TokenFactoryInterface
    {
        return $this->container->get(TokenFactoryInterface::class);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param callable $next
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next): ResponseInterface
    {
        list($type, $secret) = $this->parseAuthHeader($request);
        switch (strtolower($type)) {
            case 'basic':
                list($email, $password) = $this->parseCredentials($secret);
                $user = $this->getAuthenticator()->authenticateByCredentials($email, $password);

                /** @var ResponseInterface $response */
                $response = $next($this->setUser($request, $user), $response);

                /**
                 * Creating the token after the controller is important when changing a user password
                 * In this case the token has to be created *AFTER* the password has been changed, because otherwise
                 * it would be considered invalid for the next request
                 *
                 * @see Authenticator::authenticateByToken()
                 */

                $token = $this->getTokenFactory()->create(
                    $user,
                    new DateTimeImmutable('now + 1 year')
                );
                return $response->withHeader('X-Token', $token->encode());

            case 'bearer':
                if ($this->credentialsRequired) {
                    throw new AuthenticationException('Bearer authentication is not allowed on this route');
                }
                $user = $this->getAuthenticator()->authenticateByToken(JsonWebToken::decode($secret));
                return $next($this->setUser($request, $user), $response);
        }

        throw new AuthenticationException('Unsupported authentication type');
    }

    private function setUser(ServerRequestInterface $request, User $user): ServerRequestInterface
    {
        return $request->withAttribute('user', $user);
    }

    /**
     * @param RequestInterface $request
     * @return string[]
     */
    private function parseAuthHeader(RequestInterface $request): array
    {
        list($authHeader) = $request->getHeader('Authorization');
        if (!is_string($authHeader)) {
            throw new AuthenticationException('Missing Authorization header');
        }

        $parts  = explode(' ', $authHeader, 2);
        $secret = count($parts) > 1 ? $parts[1] : $parts[0];
        $type   = count($parts) > 1 ? $parts[0]: 'bearer';
        if (!is_string($type) || !is_string($secret)) {
            throw new AuthenticationException('Malformed Authorization Header');
        }

        return [$type, $secret];
    }

    /**
     * @param string $encoded
     * @return array
     */
    private function parseCredentials(string $encoded): array
    {
        list($email, $password) = explode(':', base64_decode($encoded), 2);
        if (!is_string($email) || !is_string($password)) {
            throw new AuthenticationException('Malformed BasicAuth credentials');
        }

        return [$email, $password];
    }
}