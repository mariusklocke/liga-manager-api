<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read;

use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\EqualityFilter;
use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\Filter;
use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\Pagination;

class EventRepository extends AbstractRepository
{
    protected function getFieldDefinitions(): array
    {
        return [
            'id' => Hydrator::TYPE_STRING,
            'occurred_at' => Hydrator::TYPE_STRING,
            'payload' => Hydrator::TYPE_SERIALIZED_ARRAY,
            'type' => Hydrator::TYPE_STRING
        ];
    }

    /**
     * @param string $id
     * @return array|null
     */
    public function findById(string $id): ?array
    {
        return $this->hydrator->hydrateOne($this->gateway->fetch(
            'events',
            [],
            [new EqualityFilter('id', Filter::MODE_INCLUDE, [$id])]
        ));
    }

    /**
     * @param iterable|array $filters
     * @param iterable|array $sortings
     * @param Pagination|null $pagination
     * @return array
     */
    public function findMany(
        iterable    $filters = [],
        iterable    $sortings = [],
        ?Pagination $pagination = null
    ): array {
        return $this->hydrator->hydrateMany($this->gateway->fetch(
            'events',
            [],
            $filters,
            $sortings,
            $pagination
        ));
    }
}
