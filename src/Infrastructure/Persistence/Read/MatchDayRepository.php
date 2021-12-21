<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read;

class MatchDayRepository extends AbstractRepository
{
    protected function getTableName(): string
    {
        return 'match_days';
    }

    protected function getFieldDefinitions(): array
    {
        return [
            'id' => Hydrator::TYPE_STRING,
            'season_id' => Hydrator::TYPE_STRING,
            'tournament_id' => Hydrator::TYPE_STRING,
            'number' => Hydrator::TYPE_INT,
            'start_date' => Hydrator::TYPE_STRING,
            'end_date' => Hydrator::TYPE_STRING
        ];
    }
}
