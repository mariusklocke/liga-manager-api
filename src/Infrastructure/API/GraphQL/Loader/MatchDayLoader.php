<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL\Loader;

use HexagonalPlayground\Infrastructure\Persistence\Read\AbstractRepository;

class MatchDayLoader extends AbstractRepository
{
    /**
     * @param array $seasonIds
     * @return array
     */
    public function loadBySeasonId(array $seasonIds): array
    {
        if (empty($seasonIds)) {
            return [];
        }

        $placeholders = $this->getPlaceholders($seasonIds);
        $startDate = $this->getDateFormat('start_date');
        $endDate   = $this->getDateFormat('end_date');
        $query     = <<<SQL
  SELECT id, season_id, tournament_id, number, $startDate, $endDate
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
    public function loadByTournamentId(array $tournamentIds): array
    {
        if (empty($tournamentIds)) {
            return [];
        }

        $placeholders = $this->getPlaceholders($tournamentIds);
        $startDate = $this->getDateFormat('start_date');
        $endDate   = $this->getDateFormat('end_date');
        $query     = <<<SQL
  SELECT id, season_id, tournament_id, number, $startDate, $endDate
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