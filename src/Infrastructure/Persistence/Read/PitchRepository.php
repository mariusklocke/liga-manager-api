<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read;

use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\EqualityFilter;
use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\Filter;

class PitchRepository extends AbstractRepository
{
    protected function getTableName(): string
    {
        return 'pitches';
    }

    protected function getFieldDefinitions(): array
    {
        return [
            'id' => Hydrator::TYPE_STRING,
            'label' => Hydrator::TYPE_STRING,
            'location_longitude' => Hydrator::TYPE_FLOAT,
            'location_latitude' => Hydrator::TYPE_FLOAT,
            'contact' => [
                'email' => Hydrator::TYPE_STRING,
                'first_name' => Hydrator::TYPE_STRING,
                'last_name' => Hydrator::TYPE_STRING,
                'phone' => Hydrator::TYPE_STRING
            ]
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
