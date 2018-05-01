<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence;

use DateTimeInterface;
use HexagonalPlayground\Application\EventSerializer;
use HexagonalPlayground\Application\EventStoreInterface;
use HexagonalPlayground\Domain\DomainEvent;
use MongoDB\Collection;

class MongoEventStore implements EventStoreInterface
{
    /** @var Collection */
    private $collection;

    /** @var EventSerializer */
    private $serializer;

    /**
     * @param Collection $collection
     * @param EventSerializer $serializer
     */
    public function __construct(Collection $collection, EventSerializer $serializer)
    {
        $this->collection = $collection;
        $this->serializer = $serializer;
    }

    /**
     * @param DomainEvent $event
     */
    public function append(DomainEvent $event): void
    {
        $this->collection->insertOne($this->serializer->serialize($event));
    }

    /**
     * @param string|null $eventType
     * @param DateTimeInterface|null $from
     * @param DateTimeInterface|null $to
     * @return DomainEvent[]
     */
    public function findMany(string $eventType = null, DateTimeInterface $from = null, DateTimeInterface $to = null): array
    {
        $filter = [];
        if (null !== $eventType) {
            $filter['type'] = $eventType;
        }
        if (null !== $from) {
            $filter['occurredAt']['$gte'] = $from->getTimestamp();
        }
        if (null !== $to) {
            $filter['occurredAt']['$lte'] = $to->getTimestamp();
        }

        $objects = [];
        foreach ($this->collection->find($filter) as $item) {
            $objects[] = $this->serializer->deserialize($item);
        }
        return $objects;
    }

    public function clear(): void
    {
        $this->collection->drop();
    }
}