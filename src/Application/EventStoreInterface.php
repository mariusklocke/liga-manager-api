<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application;

use HexagonalPlayground\Domain\DomainEvent;

interface EventStoreInterface
{
    public function append(DomainEvent $event);

    public function findAll();
}