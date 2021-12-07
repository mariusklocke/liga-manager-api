<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Bus;

use HexagonalPlayground\Application\Command\CommandInterface;
use HexagonalPlayground\Application\Handler\AuthAwareHandler;
use HexagonalPlayground\Application\OrmTransactionWrapperInterface;
use HexagonalPlayground\Application\Security\AuthChecker;
use HexagonalPlayground\Application\Security\AuthContext;
use Psr\Log\LoggerInterface;

class CommandBus
{
    /** @var HandlerResolver */
    private $resolver;

    /** @var OrmTransactionWrapperInterface */
    private $transactionWrapper;

    /** @var AuthChecker */
    private $authChecker;

    /** @var LoggerInterface */
    private $logger;

    /**
     * @param HandlerResolver $resolver
     * @param OrmTransactionWrapperInterface $transactionWrapper
     * @param LoggerInterface $logger
     */
    public function __construct(HandlerResolver $resolver, OrmTransactionWrapperInterface $transactionWrapper, LoggerInterface $logger)
    {
        $this->resolver = $resolver;
        $this->transactionWrapper = $transactionWrapper;
        $this->authChecker = new AuthChecker();
        $this->logger = $logger;
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
                $this->authChecker->check($authContext);
                $handler($command, $authContext);
            } else {
                $handler($command);
            }
        });
        $this->logger->info('Successfully executed command.', [
            'command' => get_class($command),
            'userId' => $authContext !== null ? $authContext->getUser()->getId() : null
        ]);
    }
}
