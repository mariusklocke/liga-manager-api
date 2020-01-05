<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Bus;

use HexagonalPlayground\Application\Command\CommandInterface;
use HexagonalPlayground\Application\Handler\AuthAwareHandler;
use HexagonalPlayground\Application\OrmTransactionWrapperInterface;
use HexagonalPlayground\Application\Security\AuthContext;

class CommandBus
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
     * @param CommandInterface $command
     * @param AuthContext|null $authContext
     */
    public function execute(CommandInterface $command, ?AuthContext $authContext = null): void
    {
        $handler = $this->resolver->resolve($command);
        $this->transactionWrapper->transactional(function() use ($handler, $command, $authContext) {
            if ($handler instanceof AuthAwareHandler) {
                $handler($command, $authContext);
            } else {
                $handler($command);
            }
        });
    }
}
