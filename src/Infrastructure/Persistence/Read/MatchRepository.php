<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read;

use HexagonalPlayground\Infrastructure\Persistence\Read\Field\DateTimeField;
use HexagonalPlayground\Infrastructure\Persistence\Read\Field\IntegerField;
use HexagonalPlayground\Infrastructure\Persistence\Read\Field\StringField;

class MatchRepository extends AbstractRepository
{
    protected function getTableName(): string
    {
        return 'matches';
    }

    protected function getFieldDefinitions(): array
    {
        return [
            new StringField('id', false),
            new StringField('match_day_id', false),
            new StringField('home_team_id', false),
            new StringField('guest_team_id', false),
            new StringField('pitch_id', true),
            new DateTimeField('kickoff', true),
            new DateTimeField('cancelled_at', true),
            new StringField('cancellation_reason', true),
            new IntegerField('home_score', true),
            new IntegerField('guest_score', true)
        ];
    }
}
