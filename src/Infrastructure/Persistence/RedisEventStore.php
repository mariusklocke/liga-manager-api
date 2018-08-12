<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence;

use HexagonalPlayground\Application\EventSerializer;
use HexagonalPlayground\Application\EventStoreInterface;
use HexagonalPlayground\Domain\Event\Event;
use Redis;

class RedisEventStore implements EventStoreInterface
{
    private const SET_KEY = 'events';

    /** @var Redis */
    private $redis;

    /** @var EventSerializer */
    private $serializer;

    /**
     * @param Redis $redis An already connected Redis instance
     * @param EventSerializer $serializer
     */
    public function __construct(Redis $redis, EventSerializer $serializer)
    {
        $this->redis = $redis;
        $this->serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    public function append(Event $event): void
    {
        $this->redis->zAdd(self::SET_KEY, $event->getOccurredAt()->getTimestamp(), json_encode($this->serializer->serialize($event)));
    }

    /**
     * {@inheritdoc}
     */
    public function findAll(): array
    {
        $result = [];
        foreach ($this->redis->zRange(self::SET_KEY, 0, -1) as $value) {
            $result[] = $this->serializer->deserialize(json_decode($value));
        }
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function clear(): void
    {
        $this->redis->del(self::SET_KEY);
    }
}