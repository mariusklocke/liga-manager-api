<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read;

use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\EqualityFilter;
use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\Filter;

class PitchRepository extends AbstractRepository
{
    protected function getFieldDefinitions(): array
    {
        return [
            'id' => Hydrator::TYPE_STRING,
            'label' => Hydrator::TYPE_STRING,
            'location_longitude' => Hydrator::TYPE_FLOAT,
            'location_latitude' => Hydrator::TYPE_FLOAT,
            'contact' => function (array $row): ?array {
                $contact = [
                    'email' => $row['contact_email'],
                    'first_name' => $row['contact_first_name'],
                    'last_name' => $row['contact_last_name'],
                    'phone' => $row['contact_phone']
                ];

                foreach ($contact as $value) {
                    if ($value !== null) {
                        return $contact;
                    }
                }

                return null;
            }
        ];
    }

    /**
     * @param iterable|Filter[] $filters
     * @return array
     */
    public function findMany(iterable $filters = []): array
    {
        return $this->hydrator->hydrateMany($this->gateway->fetch(
            'pitches',
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
        return $this->hydrator->hydrateOne($this->gateway->fetch(
            'pitches',
            [],
            [new EqualityFilter('id', Filter::MODE_INCLUDE, [$id])]
        ));
    }
}
