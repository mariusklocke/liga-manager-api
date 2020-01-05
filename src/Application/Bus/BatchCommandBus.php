<?php declare(strict_types=1);

namespace HexagonalPlayground\Application\Bus;

use HexagonalPlayground\Application\Handler\AuthAwareHandler;
use HexagonalPlayground\Application\OrmTransactionWrapperInterface;
use HexagonalPlayground\Application\Security\AuthContext;

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
     * @param AuthContext|null $authContext
     */
    public function execute(CommandQueue $commandQueue, ?AuthContext $authContext): void
    {
        $this->transactionWrapper->transactional(function () use ($commandQueue, $authContext) {
            foreach ($commandQueue->getIterator() as $command) {
                $handler = $this->resolver->resolve($command);
                if ($handler instanceof AuthAwareHandler) {
                    $handler($command, $authContext);
                } else {
                    $handler($command);
                }
            }
        });
    }
}