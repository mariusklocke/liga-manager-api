<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read;

use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\EqualityFilter;
use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\Filter;
use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\Sorting;

class RankingRepository extends AbstractRepository
{
    protected function getFieldDefinitions(): array
    {
        return [
            'season_id' => Hydrator::TYPE_STRING,
            'updated_at' => Hydrator::TYPE_DATETIME,
            'positions' => [
                'season_id' => Hydrator::TYPE_STRING,
                'team_id' => Hydrator::TYPE_STRING,
                'sort_index' => Hydrator::TYPE_INT,
                'number' => Hydrator::TYPE_INT,
                'matches' => Hydrator::TYPE_INT,
                'wins' => Hydrator::TYPE_INT,
                'draws' => Hydrator::TYPE_INT,
                'losses' => Hydrator::TYPE_INT,
                'scored_goals' => Hydrator::TYPE_INT,
                'conceded_goals' => Hydrator::TYPE_INT,
                'points' => Hydrator::TYPE_INT
            ],
            'penalties' => [
                'id' => Hydrator::TYPE_STRING,
                'season_id' => Hydrator::TYPE_STRING,
                'team_id' => Hydrator::TYPE_STRING,
                'reason' => Hydrator::TYPE_STRING,
                'points' => Hydrator::TYPE_INT,
                'created_at' => Hydrator::TYPE_DATETIME
            ]
        ];
    }

    /**
     * @param string $seasonId
     * @return array|null
     */
    public function findRanking(string $seasonId): ?array
    {
        $filters = [new EqualityFilter('season_id', Filter::MODE_INCLUDE, [$seasonId])];

        foreach ($this->gateway->fetch('rankings', [], $filters) as $ranking) {
            $ranking['positions'] = $this->findRankingPositions($seasonId);
            $ranking['penalties'] = $this->findRankingPenalties($seasonId);

            return $this->hydrator->hydrateOne([$ranking]);
        }

        return null;
    }

    /**
     * @param string $seasonId
     * @return array
     */
    private function findRankingPositions(string $seasonId): array
    {
        $result = $this->gateway->fetch(
            'ranking_positions',
            [],
            [new EqualityFilter('season_id', Filter::MODE_INCLUDE, [$seasonId])],
            [new Sorting('sort_index', Sorting::DIRECTION_ASCENDING)]
        );

        return iterator_to_array($result);
    }

    /**
     * @param string $seasonId
     * @return array
     */
    private function findRankingPenalties(string $seasonId): array
    {
        $result = $this->gateway->fetch(
            'ranking_penalties',
            [],
            [new EqualityFilter('season_id', Filter::MODE_INCLUDE, [$seasonId])],
            [new Sorting('created_at', Sorting::DIRECTION_ASCENDING)]
        );

        return iterator_to_array($result);
    }
}
