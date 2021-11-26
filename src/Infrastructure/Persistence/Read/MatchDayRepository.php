<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read;

class MatchDayRepository extends AbstractRepository
{
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
  SELECT id, season_id, tournament_id, number, start_date, end_date
  FROM match_days
  WHERE season_id IN ($placeholders)
  ORDER BY number ASC
SQL;

        $result = [];
        foreach ($this->getDb()->fetchAll($query, $seasonIds) as $row) {
            $result[$row['season_id']][] = $row;
        }

        return $result;
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
  SELECT id, season_id, tournament_id, number, start_date, end_date
  FROM match_days
  WHERE tournament_id IN ($placeholders)
  ORDER BY number ASC
SQL;

        $result = [];
        foreach ($this->getDb()->fetchAll($query, $tournamentIds) as $row) {
            $result[$row['tournament_id']][] = $row;
        }

        return $result;
    }
}
