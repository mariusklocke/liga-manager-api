<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application;

use HexagonalPlayground\Domain\DomainEvent;

interface EventStoreInterface
{
    /**
     * Append an event
     *
     * @param DomainEvent $event
     */
    public function append(DomainEvent $event): void;

    /**
     * Find all events
     *
     * @return array
     */
    public function findAll(): array;

    /**
     * Clear all events
     */
    public function clear(): void;
}