<?php
/**
 * MatchRepository.php
 *
 * @author    Marius Klocke <marius.klocke@eventim.de>
 * @copyright Copyright (c) 2017, CTS EVENTIM Solutions GmbH
 */

namespace HexagonalPlayground\Application\Repository;

class MatchRepository extends AbstractRepository
{
    /**
     * @param string $seasonId
     * @param int    $matchDay
     * @return array
     */
    public function findMatches(string $seasonId, int $matchDay) : array
    {
        $query = 'SELECT * FROM `matches` WHERE season_id = :seasonId AND match_day = :matchDay';
        return $this->getDb()->fetchAll($query, ['seasonId' => $seasonId, 'matchDay' => $matchDay]);
    }

    /**
     * @param string $matchId
     * @return array|null
     */
    public function findMatchById(string $matchId)
    {
        return $this->getDb()->fetchFirstRow('SELECT * FROM `matches` WHERE `id` = :id', ['id' => $matchId]);
    }

    /**
     * @param string $seasonId
     * @return int
     */
    public function countMatchesInSeason(string $seasonId) : int
    {
        $query = 'SELECT COUNT(id) FROM `matches` WHERE `season_id` = :seasonId';
        $count = $this->getDb()->fetchSingleColumn($query, ['seasonId' => $seasonId]);
        return (int) $count;
    }

    /**
     * @param string $seasonId
     * @return int
     */
    public function countMatchDaysInSeason(string $seasonId) : int
    {
        $query = 'SELECT COUNT(DISTINCT `match_day`) FROM `matches` WHERE `season_id` = :seasonId';
        $count = $this->getDb()->fetchSingleColumn($query, ['seasonId' => $seasonId]);
        return (int) $count;
    }
}