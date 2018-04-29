<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence;

use HexagonalPlayground\Application\EventStoreInterface;
use HexagonalPlayground\Domain\DomainEvent;
use MongoDB\Collection;

class MongoEventStore implements EventStoreInterface
{
    /** @var Collection */
    private $collection;

    /**
     * @param Collection $collection
     */
    public function __construct(Collection $collection)
    {
        $this->collection = $collection;
    }

    public function append(DomainEvent $event): void
    {
        $this->collection->insertOne($event->toArray());
    }

    public function findAll(): array
    {
        return $this->collection->find()->toArray();
    }
}