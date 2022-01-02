<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read;

use HexagonalPlayground\Infrastructure\Persistence\Read\Field\DateTimeField;
use HexagonalPlayground\Infrastructure\Persistence\Read\Field\IntegerField;
use HexagonalPlayground\Infrastructure\Persistence\Read\Field\StringField;

class MatchDayRepository extends AbstractRepository
{
    protected function getTableName(): string
    {
        return 'match_days';
    }

    protected function getFieldDefinitions(): array
    {
        return [
            new StringField('id', false),
            new StringField('season_id', false),
            new StringField('tournament_id', false),
            new IntegerField('number', false),
            new DateTimeField('start_date', false),
            new DateTimeField('end_date', false)
        ];
    }
}
