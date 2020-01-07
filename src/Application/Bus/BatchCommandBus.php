<?php declare(strict_types=1);

namespace HexagonalPlayground\Application\Bus;

use HexagonalPlayground\Application\Handler\AuthAwareHandler;
use HexagonalPlayground\Application\OrmTransactionWrapperInterface;
use HexagonalPlayground\Application\Security\AuthChecker;
use HexagonalPlayground\Application\Security\AuthContext;

class BatchCommandBus
{
    /** @var HandlerResolver */
    private $resolver;

    /** @var OrmTransactionWrapperInterface */
    private $transactionWrapper;

    /** @var AuthChecker */
    private $authChecker;

    /**
     * @param HandlerResolver $resolver
     * @param OrmTransactionWrapperInterface $transactionWrapper
     */
    public function __construct(HandlerResolver $resolver, OrmTransactionWrapperInterface $transactionWrapper)
    {
        $this->resolver = $resolver;
        $this->transactionWrapper = $transactionWrapper;
        $this->authChecker = new AuthChecker();
    }

    /**
     * @param CommandQueue $commandQueue
     * @param AuthContext|null $authContext
     */
    public function execute(CommandQueue $commandQueue, ?AuthContext $authContext = null): void
    {
        $this->transactionWrapper->transactional(function () use ($commandQueue, $authContext) {
            foreach ($commandQueue->getIterator() as $command) {
                $handler = $this->resolver->resolve($command);
                if ($handler instanceof AuthAwareHandler) {
                    $this->authChecker->check($authContext);
                    $handler($command, $authContext);
                } else {
                    $handler($command);
                }
            }
        });
    }
}