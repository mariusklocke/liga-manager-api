<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Bus;

use HexagonalPlayground\Application\Command\CommandInterface;
use HexagonalPlayground\Application\Exception\CommandBusException;
use HexagonalPlayground\Application\OrmTransactionWrapperInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;

abstract class CommandBus
{
    /** @var ContainerInterface */
    protected $container;
    /** @var OrmTransactionWrapperInterface */
    protected $transactionWrapper;

    /**
     * @param ContainerInterface $container
     * @param OrmTransactionWrapperInterface $transactionWrapper
     */
    public function __construct(ContainerInterface $container, OrmTransactionWrapperInterface $transactionWrapper)
    {
        $this->container = $container;
        $this->transactionWrapper = $transactionWrapper;
    }

    /**
     * @param CommandInterface $command
     * @return object
     * @throws CommandBusException If the container does not contain a valid handler for given Command class
     */
    protected function getHandler(CommandInterface $command)
    {
        try {
            $handler = $this->container->get(get_class($command));
        } catch (ContainerExceptionInterface $e) {
            throw new CommandBusException('Cannot find a CommandHandler for ' . get_class($command), 0, $e);
        }

        if (!is_object($handler) || !method_exists($handler, 'handle')) {
            throw new CommandBusException('Command Handler for ' . get_class($command) . ' does not implement handle()');
        }

        return $handler;
    }
}
