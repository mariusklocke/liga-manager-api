<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read;

use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\EqualityFilter;
use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\Filter;
use HexagonalPlayground\Infrastructure\Persistence\Read\Field\EmbeddedObjectField;
use HexagonalPlayground\Infrastructure\Persistence\Read\Field\FloatField;
use HexagonalPlayground\Infrastructure\Persistence\Read\Field\StringField;

class PitchRepository extends AbstractRepository
{
    protected function getTableName(): string
    {
        return 'pitches';
    }

    protected function getFieldDefinitions(): array
    {
        return [
            new StringField('id', false),
            new StringField('label', false),
            new FloatField('location_longitude', false),
            new FloatField('location_latitude', false),
            new EmbeddedObjectField('contact', true, [
                new StringField('email', false),
                new StringField('first_name', false),
                new StringField('last_name', false),
                new StringField('phone', false)
            ])
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
