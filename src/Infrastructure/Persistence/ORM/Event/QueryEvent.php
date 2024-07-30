<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\ORM\Event;

class QueryEvent
{
    private string $query;
    private array $params;

    public function __construct(string $query, array $params = [])
    {
        $this->query = $query;
        $this->params = $params;
    }

    public function getQuery(): string
    {
        return $this->query;
    }

    public function getParams(): array
    {
        return $this->params;
    }
}
