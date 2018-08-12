<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application;

use HexagonalPlayground\Domain\Event\Event;
use HexagonalPlayground\Domain\Event\Subscriber;

class EventStoreSubscriber implements Subscriber
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
     * @param Event $event
     */
    public function handle(Event $event)
    {
        $this->store->append($event);
    }
}