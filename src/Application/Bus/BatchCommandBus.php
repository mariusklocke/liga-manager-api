<?php declare(strict_types=1);

namespace HexagonalPlayground\Application\Bus;

use HexagonalPlayground\Application\OrmTransactionWrapperInterface;

class BatchCommandBus
{
    /** @var HandlerResolver */
    private $resolver;

    /** @var OrmTransactionWrapperInterface */
    private $transactionWrapper;

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
     * @param CommandQueue $commandQueue
     */
    public function execute(CommandQueue $commandQueue): void
    {
        $this->transactionWrapper->transactional(function () use ($commandQueue) {
            foreach ($commandQueue->getIterator() as $command) {
                $handler = $this->resolver->resolve($command);
                $handler($command);
            }
        });
    }
}