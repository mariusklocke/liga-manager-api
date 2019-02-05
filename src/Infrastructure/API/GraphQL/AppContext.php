<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL;

use HexagonalPlayground\Application\Exception\AuthenticationException;
use HexagonalPlayground\Domain\User;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

class AppContext
{
    /** @var ServerRequestInterface */
    private $request;

    /** @var ContainerInterface */
    private $container;

    /**
     * @param ServerRequestInterface $request
     * @param ContainerInterface $container
     */
    public function __construct(ServerRequestInterface $request, ContainerInterface $container)
    {
        $this->request = $request;
        $this->container = $container;
    }

    /**
     * @return ServerRequestInterface
     */
    public function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }

    public function getAuthenticatedUser(): User
    {
        return new User('fake@example.com', '123456', 'foo', 'bar', 'admin');
        $user = $this->request->getAttribute('user');
        if ($user instanceof User) {
            return $user;
        }

        throw new AuthenticationException('Unauthenticated request');
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }
}