<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read;

class RankingRepository extends AbstractRepository
{
    /**
     * @param string $seasonId
     * @return array|null
     */
    public function findRanking(string $seasonId)
    {
        return $this->getDb()->fetchFirstRow(
            'SELECT * FROM rankings WHERE season_id = ?',
            [$seasonId]
        );
    }

    /**
     * @param string $seasonId
     * @return array
     */
    public function findRankingPositions(string $seasonId) : array
    {
        return $this->getDb()->fetchAll(
            'SELECT * FROM ranking_positions WHERE season_id = ? ORDER BY sort_index ASC',
            [$seasonId]
        );
    }
}