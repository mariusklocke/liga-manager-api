<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence;

use HexagonalPlayground\Application\EventSerializer;
use HexagonalPlayground\Domain\DomainEvent;
use HexagonalPlayground\Domain\EventSubscriber;
use Redis;

class RedisEventPublisher implements EventSubscriber
{
    /** @var Redis */
    private $redis;

    /** @var EventSerializer */
    private $serializer;

    /**
     * @param Redis $redis
     * @param EventSerializer $serializer
     */
    public function __construct(Redis $redis, EventSerializer $serializer)
    {
        $this->redis = $redis;
        $this->serializer = $serializer;
    }

    /**
     * Handles a DomainEvent by publishing it on a redis channel
     *
     * @param DomainEvent $event
     */
    public function handle(DomainEvent $event): void
    {
        $this->redis->publish('events', json_encode($this->serializer->serialize($event)));
    }
}