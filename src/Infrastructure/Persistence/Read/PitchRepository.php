<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read;

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
            new EmbeddedObjectField('location', true, [
                new FloatField('longitude', false),
                new FloatField('latitude', false)
            ]),
            new EmbeddedObjectField('contact', true, [
                new StringField('email', false),
                new StringField('first_name', false),
                new StringField('last_name', false),
                new StringField('phone', false)
            ])
        ];
    }
}
