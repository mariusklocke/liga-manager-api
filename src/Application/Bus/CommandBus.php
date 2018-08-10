<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Bus;

use HexagonalPlayground\Application\Command\CommandInterface;
use HexagonalPlayground\Application\OrmTransactionWrapperInterface;
use UnexpectedValueException;

abstract class CommandBus
{
    /** @var HandlerResolver */
    private $resolver;

    /** @var OrmTransactionWrapperInterface */
    protected $transactionWrapper;

    /**
     * @param HandlerResolver $resolver
     * @param OrmTransactionWrapperInterface $transactionWrapper
     */
    public function __construct(HandlerResolver $resolver, OrmTransactionWrapperInterface $transactionWrapper)
    {
        $this->resolver = $resolver;
        $this->transactionWrapper = $transactionWrapper;
    }

    /**
     * @param CommandInterface $command
     * @return callable
     * @throws UnexpectedValueException If the resolver does not return a valid handler for the given command
     */
    protected function getHandler(CommandInterface $command): callable
    {
        /** @var callable $handler */
        $handler = $this->resolver->resolve($command);
        if (!is_callable($handler)) {
            throw new UnexpectedValueException('Command Handler for ' . get_class($command) . ' is not a callable');
        }

        return $handler;
    }
}
