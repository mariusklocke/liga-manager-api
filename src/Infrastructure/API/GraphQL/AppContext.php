<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL;

use HexagonalPlayground\Infrastructure\API\Security\AuthAware;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

class AppContext
{
    use AuthAware;

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

    /**
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }
}