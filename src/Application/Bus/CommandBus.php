<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Bus;

use HexagonalPlayground\Application\Command\CommandInterface;
use HexagonalPlayground\Application\Handler\AuthAwareHandler;
use HexagonalPlayground\Application\OrmTransactionWrapperInterface;
use HexagonalPlayground\Application\Repository\EventRepositoryInterface;
use HexagonalPlayground\Application\Security\AuthChecker;
use HexagonalPlayground\Application\Security\AuthContext;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;

class CommandBus
{
    /** @var HandlerResolver */
    private HandlerResolver $resolver;

    /** @var OrmTransactionWrapperInterface */
    private OrmTransactionWrapperInterface $transactionWrapper;

    /** @var EventDispatcherInterface */
    private EventDispatcherInterface $eventDispatcher;

    /** @var EventRepositoryInterface */
    private EventRepositoryInterface $eventRepository;

    /** @var AuthChecker */
    private AuthChecker $authChecker;

    /** @var LoggerInterface */
    private LoggerInterface $logger;

    /**
     * @param HandlerResolver $resolver
     * @param OrmTransactionWrapperInterface $transactionWrapper
     * @param EventDispatcherInterface $eventDispatcher
     * @param EventRepositoryInterface $eventRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        HandlerResolver $resolver,
        OrmTransactionWrapperInterface $transactionWrapper,
        EventDispatcherInterface $eventDispatcher,
        EventRepositoryInterface $eventRepository,
        LoggerInterface $logger
    ) {
        $this->resolver = $resolver;
        $this->transactionWrapper = $transactionWrapper;
        $this->eventDispatcher = $eventDispatcher;
        $this->eventRepository = $eventRepository;
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

        $events = $this->transactionWrapper->transactional(function() use ($handler, $command, $authContext) {
            if ($handler instanceof AuthAwareHandler) {
                $this->authChecker->check($authContext);
                $events = $handler($command, $authContext);
            } else {
                $events = $handler($command);
            }

            foreach ($events as $event) {
                $this->eventRepository->save($event);
            }

            return $events;
        });

        foreach ($events as $event) {
            $this->eventDispatcher->dispatch($event);
        }

        $this->logger->info('Successfully executed command.', [
            'command' => get_class($command),
            'userId' => $authContext !== null ? $authContext->getUser()->getId() : null,
            'events' => count($events)
        ]);
    }
}
