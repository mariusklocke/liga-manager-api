<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\ORM\Event;

class QueryEvent
{
    private string $query;
    private array $params;
}
