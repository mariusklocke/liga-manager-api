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
  SELECT *
  FROM match_days
  WHERE season_id IN ($placeholders)
  ORDER BY number ASC
SQL;

        $result = [];
        foreach ($this->getDb()->fetchAll($query, $seasonIds) as $row) {
            $result[$row['season_id']][] = $this->hydrate($row);
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
  SELECT *
  FROM match_days
  WHERE tournament_id IN ($placeholders)
  ORDER BY number ASC
SQL;

        $result = [];
        foreach ($this->getDb()->fetchAll($query, $tournamentIds) as $row) {
            $result[$row['tournament_id']][] = $this->hydrate($row);
        }

        return $result;
    }

    protected function hydrate(array $row): array
    {
        return [
            'id' => $this->hydrator->string($row['id']),
            'season_id' => $this->hydrator->string($row['season_id']),
            'tournament_id' => $this->hydrator->string($row['tournament_id']),
            'number' => $this->hydrator->int($row['number']),
            'start_date' => $this->hydrator->string($row['start_date']),
            'end_date' => $this->hydrator->string($row['end_date'])
        ];
    }
}
