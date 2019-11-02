<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Bus;

use HexagonalPlayground\Application\Command\CommandInterface;
use Psr\Container\ContainerInterface;

class ContainerHandlerResolver implements HandlerResolver
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
     * @param CommandInterface $command
     * @return callable
     */
    public function resolve(CommandInterface $command): callable
    {
        return $this->container->get(str_replace('Command', 'Handler', get_class($command)));
    }
}