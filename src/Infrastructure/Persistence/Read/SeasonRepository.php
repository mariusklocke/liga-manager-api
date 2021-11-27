<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read;

class SeasonRepository extends AbstractRepository
{
    /**
     * @return array
     */
    public function findAllSeasons(): array
    {
        return array_map([$this, 'hydrate'], $this->getDb()->fetchAll('SELECT * FROM `seasons`'));
    }

    /**
     * @param string $id
     * @return array|null
     */
    public function findSeasonById(string $id): ?array
    {
        $row = $this->getDb()->fetchFirstRow('SELECT * FROM `seasons` WHERE `id` = ?', [$id]);

        return $row !== null ? $this->hydrate($row) : null;
    }

    /**
     * @param string $seasonId
     * @return array|null
     */
    public function findRanking(string $seasonId): ?array
    {
        $ranking = $this->getDb()->fetchFirstRow(
            "SELECT * FROM rankings WHERE season_id = ?",
            [$seasonId]
        );

        if (null === $ranking) {
            return null;
        }

        $ranking['positions'] = $this->findRankingPositions($seasonId);
        $ranking['penalties'] = $this->findRankingPenalties($seasonId);

        return $this->hydrateRanking($ranking);
    }

    /**
     * @param string $seasonId
     * @return array
     */
    private function findRankingPositions(string $seasonId): array
    {
        return $this->getDb()->fetchAll(
            'SELECT * FROM ranking_positions WHERE season_id = ? ORDER BY sort_index ASC',
            [$seasonId]
        );
    }

    /**
     * @param string $seasonId
     * @return array
     */
    private function findRankingPenalties(string $seasonId): array
    {
        $query     = <<<SQL
  SELECT *
  FROM ranking_penalties
  WHERE season_id = ?
  ORDER BY created_at ASC
SQL;

        return $this->getDb()->fetchAll($query, [$seasonId]);
    }

    protected function hydrate(array $row): array
    {
        return [
            'id' => $this->hydrator->string($row['id']),
            'name' => $this->hydrator->string($row['name']),
            'state' => $this->hydrator->string($row['state']),
            'match_day_count' => $this->hydrator->int($row['match_day_count']),
            'team_count' => $this->hydrator->int($row['team_count'])
        ];
    }

    private function hydrateRanking(array $row): array
    {
        return [
            'season_id' => $this->hydrator->string($row['season_id']),
            'updated_at' => $this->hydrator->dateTime($row['updated_at']),
            'positions' => array_map([$this, 'hydratePosition'], $row['positions']),
            'penalties' => array_map([$this, 'hydratePenalty'], $row['penalties'])
        ];
    }

    private function hydratePenalty(array $row): array
    {
        return [
            'id' => $this->hydrator->string($row['id']),
            'season_id' => $this->hydrator->string($row['season_id']),
            'team_id' => $this->hydrator->string($row['team_id']),
            'reason' => $this->hydrator->string($row['reason']),
            'points' => $this->hydrator->int($row['points']),
            'created_at' => $this->hydrator->dateTime($row['created_at'])
        ];
    }

    private function hydratePosition(array $row): array
    {
        return [
            'season_id' => $this->hydrator->string($row['season_id']),
            'team_id' => $this->hydrator->string($row['team_id']),
            'sort_index' => $this->hydrator->int($row['sort_index']),
            'number' => $this->hydrator->int($row['number']),
            'matches' => $this->hydrator->int($row['matches']),
            'wins' => $this->hydrator->int($row['wins']),
            'draws' => $this->hydrator->int($row['draws']),
            'losses' => $this->hydrator->int($row['losses']),
            'scored_goals' => $this->hydrator->int($row['scored_goals']),
            'conceded_goals' => $this->hydrator->int($row['conceded_goals']),
            'points' => $this->hydrator->int($row['points'])
        ];
    }
}
