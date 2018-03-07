<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Repository;

use DateTimeInterface;

class MatchRepository extends AbstractRepository
{
    /**
     * @param string $seasonId
     * @param int    $matchDay
     * @return array
     */
    public function findMatchesByMatchDay(string $seasonId, int $matchDay) : array
    {
        $query = 'SELECT * FROM `matches` WHERE `season_id` = ? AND `match_day` = ?';
        return $this->getDb()->fetchAll($query, [$seasonId, $matchDay]);
    }

    /**
     * @param string $seasonId
     * @param string $teamId
     * @return array
     */
    public function findMatchesByTeam(string $seasonId, string $teamId) : array
    {
        $query = <<<'SQL'
  SELECT * FROM `matches` WHERE `season_id` = ? AND (`home_team_id` = ? OR `guest_team_id` = ?)
SQL;
        return $this->getDb()->fetchAll($query, [$seasonId, $teamId, $teamId]);
    }

    /**
     * @param string $seasonId
     * @param DateTimeInterface $from
     * @param DateTimeInterface $to
     * @return array
     */
    public function findMatchesByDate(string $seasonId, DateTimeInterface $from, DateTimeInterface $to) : array
    {
        $query = 'SELECT * FROM `matches` WHERE `season_id` = ? AND `kickoff` BETWEEN ? AND ?';
        $params = [$seasonId, $from, $to];
        return $this->getDb()->fetchAll($query, $params);
    }

    /**
     * @param string $matchId
     * @return array|null
     */
    public function findMatchById(string $matchId)
    {
        return $this->getDb()->fetchFirstRow('SELECT * FROM `matches` WHERE `id` = ?', [$matchId]);
    }

    /**
     * @param string $seasonId
     * @return int
     */
    public function countMatchesInSeason(string $seasonId) : int
    {
        $query = 'SELECT COUNT(id) FROM `matches` WHERE `season_id` = ?';
        $count = $this->getDb()->fetchSingleColumn($query, [$seasonId]);
        return (int) $count;
    }
}