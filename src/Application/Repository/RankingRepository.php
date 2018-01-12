<?php
/**
 * RankingRepository.php
 *
 * @author    Marius Klocke <marius.klocke@eventim.de>
 * @copyright Copyright (c) 2017, CTS EVENTIM Solutions GmbH
 */

namespace HexagonalPlayground\Application\Repository;

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