<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure;

use HexagonalPlayground\Application\Bus\HandlerResolver;
use HexagonalPlayground\Application\Command\CommandInterface;
use Psr\Container\ContainerInterface;

class CommandHandlerResolver implements HandlerResolver
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
     * @return object
     */
    public function resolve(CommandInterface $command)
    {
        return $this->container->get(get_class($command));
    }
}