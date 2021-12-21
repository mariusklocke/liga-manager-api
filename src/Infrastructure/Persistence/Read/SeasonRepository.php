<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read;

use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\EqualityFilter;
use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\Filter;

class SeasonRepository extends AbstractRepository
{
    protected function getTableName(): string
    {
        return 'seasons';
    }

    protected function getFieldDefinitions(): array
    {
        return [
            'id' => Hydrator::TYPE_STRING,
            'name' => Hydrator::TYPE_STRING,
            'state' => Hydrator::TYPE_STRING,
            'match_day_count' => Hydrator::TYPE_INT,
            'team_count' => Hydrator::TYPE_INT
        ];
    }

    /**
     * @param string $id
     * @return array|null
     */
    public function findById(string $id): ?array
    {
        return $this->hydrator->hydrateOne($this->gateway->fetch(
            $this->getTableName(),
            [],
            [new EqualityFilter('id', Filter::MODE_INCLUDE, [$id])]
        ));
    }
}
