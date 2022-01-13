<?php declare(strict_types=1);

namespace HexagonalPlayground\Application\Bus;

use HexagonalPlayground\Application\Handler\AuthAwareHandler;
use HexagonalPlayground\Application\OrmTransactionWrapperInterface;
use HexagonalPlayground\Application\Repository\EventRepositoryInterface;
use HexagonalPlayground\Application\Security\AuthChecker;
use HexagonalPlayground\Application\Security\AuthContext;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;

class BatchCommandBus
{
    /** @var HandlerResolver */
    private $resolver;

    /** @var OrmTransactionWrapperInterface */
    private $transactionWrapper;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /** @var EventRepositoryInterface */
    private $eventRepository;

    /** @var AuthChecker */
    private $authChecker;

    /** @var LoggerInterface */
    private $logger;

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
     * @param CommandQueue $commandQueue
     * @param AuthContext|null $authContext
     */
    public function execute(CommandQueue $commandQueue, ?AuthContext $authContext = null): void
    {
        $events = $this->transactionWrapper->transactional(function () use ($commandQueue, $authContext) {
            $events = [];

            foreach ($commandQueue->getIterator() as $command) {
                $handler = $this->resolver->resolve($command);
                if ($handler instanceof AuthAwareHandler) {
                    $this->authChecker->check($authContext);
                    $events = array_merge($events, $handler($command, $authContext));
                } else {
                    $events = array_merge($events, $handler($command));
                }
            }

            foreach ($events as $event) {
                $this->eventRepository->save($event);
            }

            return $events;
        });

        foreach ($events as $event) {
            $this->eventDispatcher->dispatch($event);
        }

        $this->logger->info('Successfully executed batch of commands.', [
            'commands' => $commandQueue->size(),
            'userId' => $authContext !== null ? $authContext->getUser()->getId() : null,
            'events' => count($events)
        ]);
    }
}
