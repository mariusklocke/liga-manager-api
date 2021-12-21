<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read;

use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\EqualityFilter;
use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\Filter;

class MatchRepository extends AbstractRepository
{
    protected function getTableName(): string
    {
        return 'matches';
    }

    protected function getFieldDefinitions(): array
    {
        return [
            'id' => Hydrator::TYPE_STRING,
            'match_day_id' => Hydrator::TYPE_STRING,
            'home_team_id' => Hydrator::TYPE_STRING,
            'guest_team_id' => Hydrator::TYPE_STRING,
            'pitch_id' => Hydrator::TYPE_STRING,
            'kickoff' => Hydrator::TYPE_DATETIME,
            'cancelled_at' => Hydrator::TYPE_DATETIME,
            'cancellation_reason' => Hydrator::TYPE_STRING,
            'home_score' => Hydrator::TYPE_INT,
            'guest_score' => Hydrator::TYPE_INT
        ];
    }

    /**
     * @param string $matchId
     * @return array|null
     */
    public function findById(string $matchId): ?array
    {
        return $this->hydrator->hydrateOne($this->gateway->fetch(
            $this->getTableName(),
            [],
            [new EqualityFilter('id', Filter::MODE_INCLUDE, [$matchId])]
        ));
    }
}
