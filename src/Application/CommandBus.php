<?php

namespace HexagonalDream\Application;

use HexagonalDream\Application\Command\CommandInterface;
use HexagonalDream\Application\Exception\CommandBusException;
use HexagonalDream\Application\Exception\PersistenceExceptionInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

class CommandBus
{
    /** @var ContainerInterface */
    private $container;
    /** @var ObjectPersistenceInterface */
    private $persistence;

    /**
     * @param ContainerInterface $container
     * @param ObjectPersistenceInterface $persistence
     */
    public function __construct(ContainerInterface $container, ObjectPersistenceInterface $persistence)
    {
        $this->container = $container;
        $this->persistence = $persistence;
    }

    /**
     * @param CommandInterface $command
     * @return mixed
     * @throws CommandBusException If the container does not contain a valid handler for given Command class
     * @throws PersistenceExceptionInterface
     */
    public function execute(CommandInterface $command)
    {
        try {
            $handler = $this->container->get(get_class($command));
        } catch (ContainerExceptionInterface $e) {
            throw new CommandBusException('Cannot find a CommandHandler for ' . get_class($command), 0, $e);
        }

        if (!is_object($handler) || !method_exists($handler, 'handle')) {
            throw new CommandBusException('Command Handler for ' . get_class($command) . ' does not implement handle()');
        }

        return $this->persistence->transactional(function() use ($handler, $command) {
            return $handler->handle($command);
        });
    }
}
