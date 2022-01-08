<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read;

use HexagonalPlayground\Infrastructure\Persistence\Read\Field\IntegerField;
use HexagonalPlayground\Infrastructure\Persistence\Read\Field\StringField;

class SeasonRepository extends AbstractRepository
{
    protected function getTableName(): string
    {
        return 'seasons';
    }

    protected function getFieldDefinitions(): array
    {
        return [
            new StringField('id', false),
            new StringField('name', false),
            new StringField('state', false),
            new IntegerField('match_day_count', false),
            new IntegerField('team_count', false)
        ];
    }
}
