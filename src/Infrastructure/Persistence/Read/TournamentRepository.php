<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read;

use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\EqualityFilter;
use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\Filter;

class TournamentRepository extends AbstractRepository
{
    protected function getFieldDefinitions(): array
    {
        return [
            'id' => Hydrator::TYPE_STRING,
            'name' => Hydrator::TYPE_STRING,
            'rounds' => Hydrator::TYPE_INT
        ];
    }

    /**
     * @param iterable|Filter[] $filters
     * @return array
     */
    public function findMany(iterable $filters = []) : array
    {
        return $this->hydrateMany($this->gateway->fetch(
            'tournaments',
            [],
            $filters
        ));
    }

    /**
     * @param string $id
     * @return array|null
     */
    public function findById(string $id): ?array
    {
        return $this->hydrateOne($this->gateway->fetch(
            'tournaments',
            [],
            [new EqualityFilter('id', Filter::MODE_INCLUDE, [$id])]
        ));
    }
}
