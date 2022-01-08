<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read;

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
}
