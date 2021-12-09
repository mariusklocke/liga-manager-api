<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read;

use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\EqualityFilter;
use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\Filter;

class MatchDayRepository extends AbstractRepository
{
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

    /**
     * @param array $seasonIds
     * @return array
     */
    public function findBySeasonIds(array $seasonIds): array
    {
        if (empty($seasonIds)) {
            return [];
        }

        $result = $this->gateway->fetch(
            'match_days',
            [],
            [new EqualityFilter('season_id', Filter::MODE_INCLUDE, $seasonIds)]
        );

        return $this->hydrateMany($result, 'season_id');
    }

    /**
     * @param array $tournamentIds
     * @return array
     */
    public function findByTournamentIds(array $tournamentIds): array
    {
        if (empty($tournamentIds)) {
            return [];
        }

        $result = $this->gateway->fetch(
            'match_days',
            [],
            [new EqualityFilter('tournament_id', Filter::MODE_INCLUDE, $tournamentIds)]
        );

        return $this->hydrateMany($result, 'tournament_id');
    }
}
