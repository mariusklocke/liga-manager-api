<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application;

use HexagonalPlayground\Domain\DomainEvent;
use HexagonalPlayground\Domain\EventSubscriber;

class EventStoreSubscriber implements EventSubscriber
{
    /** @var EventStoreInterface */
    private $store;

    /**
     * @param EventStoreInterface $store
     */
    public function __construct(EventStoreInterface $store)
    {
        $this->store = $store;
    }

    /**
     * @param DomainEvent $event
     */
    public function handle(DomainEvent $event)
    {
        $this->store->append($event);
    }
}