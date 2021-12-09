<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read;

use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\EqualityFilter;
use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\Filter;
use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\Pagination;
use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\Sorting;

class MatchRepository extends AbstractRepository
{
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
        return $this->hydrateOne($this->gateway->fetch(
            'matches',
            [],
            [new EqualityFilter('id', Filter::MODE_INCLUDE, [$matchId])]
        ));
    }

    /**
     * @param iterable|Filter[] $filters
     * @param iterable|Sorting[] $sortings
     * @param Pagination|null $pagination
     * @return array
     */
    public function findMany(iterable $filters = [], iterable $sortings = [], ?Pagination $pagination = null): array
    {
        return $this->hydrateMany($this->gateway->fetch(
            'matches',
            [],
            $filters,
            $sortings,
            $pagination
        ));
    }
}
