<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read;

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

        $placeholders = $this->getPlaceholders($seasonIds);

        $query = <<<SQL
  SELECT *
  FROM match_days
  WHERE season_id IN ($placeholders)
  ORDER BY number ASC
SQL;

        return $this->hydrateMany($this->getDb()->fetchAll($query, $seasonIds), 'season_id');
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

        $placeholders = $this->getPlaceholders($tournamentIds);

        $query = <<<SQL
  SELECT *
  FROM match_days
  WHERE tournament_id IN ($placeholders)
  ORDER BY number ASC
SQL;

        return $this->hydrateMany($this->getDb()->fetchAll($query, $tournamentIds), 'tournament_id');
    }
}
