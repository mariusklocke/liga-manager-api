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
        return $this->getDb()->fetchAll('SELECT * FROM `seasons`');
    }

    /**
     * @param string $id
     * @return array|null
     */
    public function findSeasonById(string $id): ?array
    {
        return $this->getDb()->fetchFirstRow('SELECT * FROM `seasons` WHERE `id` = ?', [$id]);
    }

    /**
     * @param string $seasonId
     * @return array
     */
    public function findMatchDays(string $seasonId): array
    {
        return $this->getDb()->fetchAll(
            'SELECT * FROM `match_days` WHERE season_id = ? ORDER BY number ASC',
            [$seasonId]
        );
    }

    /**
     * @param string $seasonId
     * @return array|null
     */
    public function findRanking(string $seasonId): ?array
    {
        $updatedAt = $this->getDateFormat('updated_at');
        $ranking   = $this->getDb()->fetchFirstRow(
            "SELECT season_id, $updatedAt FROM rankings WHERE season_id = ?",
            [$seasonId]
        );
        if (null === $ranking) {
            return null;
        }

        $ranking['positions'] = $this->findRankingPositions($seasonId);
        $ranking['penalties'] = $this->findRankingPenalties($seasonId);
        return $ranking;
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
        $createdAt = $this->getDateFormat('created_at');
        $query     = <<<SQL
  SELECT id, season_id, team_id, reason, points, $createdAt
  FROM ranking_penalties
  WHERE season_id = ?
  ORDER BY created_at ASC
SQL;

        return $this->getDb()->fetchAll($query, [$seasonId]);
    }
}
