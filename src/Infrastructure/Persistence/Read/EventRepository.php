<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read;

use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\EqualityFilter;
use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\Filter;
use HexagonalPlayground\Infrastructure\Persistence\Read\Field\SerializedArrayField;
use HexagonalPlayground\Infrastructure\Persistence\Read\Field\StringField;

class EventRepository extends AbstractRepository
{
    protected function getTableName(): string
    {
        return 'events';
    }

    protected function getFieldDefinitions(): array
    {
        return [
            new StringField('id', false),
            new StringField('occurred_at', false),
            new SerializedArrayField('payload', false),
            new StringField('type', false)
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
