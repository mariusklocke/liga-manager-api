<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Security;

use HexagonalPlayground\Application\Security\Authenticator;
use HexagonalPlayground\Infrastructure\API\Exception\BadRequestException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

abstract class AuthenticationMiddleware
{
    /** @var ContainerInterface */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return Authenticator
     */
    protected function getAuthenticator(): Authenticator
    {
        return $this->container->get(Authenticator::class);
    }

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param callable $next
     * @return ResponseInterface
     */
    abstract public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next);

    /**
     * @param RequestInterface $request
     * @return array
     */
    protected function parseAuthHeader(RequestInterface $request): array
    {
        list($authHeader) = $request->getHeader('Authorization');
        if (!is_string($authHeader)) {
            return [];
        }

        $parts  = explode(' ', $authHeader, 2);
        $secret = count($parts) > 1 ? $parts[1] : $parts[0];
        $type   = count($parts) > 1 ? $parts[0]: 'bearer';
        if (!is_string($type) || !is_string($secret)) {
            throw new BadRequestException('Malformed Authorization Header');
        }

        return [$type, $secret];
    }
}