<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain;

interface EventSubscriber
{
    public function handle(DomainEvent $event);
}