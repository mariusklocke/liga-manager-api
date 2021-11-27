<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read;

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
        $ranking = $this->getDb()->fetchFirstRow(
            "SELECT * FROM rankings WHERE season_id = ?",
            [$seasonId]
        );

        if (null === $ranking) {
            return null;
        }

        $ranking['positions'] = $this->findRankingPositions($seasonId);
        $ranking['penalties'] = $this->findRankingPenalties($seasonId);

        return $this->hydrateOne($ranking);
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
}
