<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application;

use HexagonalPlayground\Domain\Event\Event;

interface EventStoreInterface
{
    /**
     * Append an event
     *
     * @param Event $event
     */
    public function append(Event $event): void;

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