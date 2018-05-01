<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application;

use DateTimeInterface;
use HexagonalPlayground\Domain\DomainEvent;

interface EventStoreInterface
{
    public function append(DomainEvent $event);

    public function findMany(string $eventType = null, DateTimeInterface $from = null, DateTimeInterface $to = null);

    public function clear();
}