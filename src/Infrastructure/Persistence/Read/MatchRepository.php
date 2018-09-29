<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read;

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
        $query = 'SELECT m.* FROM `matches` m
                  JOIN `match_days` md ON md.id = m.match_day_id
                  WHERE md.season_id = ? AND md.number = ?';
        return $this->getDb()->fetchAll($query, [$seasonId, $matchDay]);
    }

    /**
     * @param string $seasonId
     * @param string $teamId
     * @return array
     */
    public function findMatchesByTeam(string $seasonId, string $teamId) : array
    {
        $query = 'SELECT m.* FROM `matches` m
                  JOIN `match_days` md ON md.id = m.match_day_id
                  WHERE md.season_id = ? AND (m.home_team_id = ? OR m.guest_team_id = ?)';
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
        $query = 'SELECT m.* FROM `matches` m
                  JOIN `match_days` md ON md.id = m.match_day_id
                  WHERE md.season_id = ? AND m.kickoff BETWEEN ? AND ?';
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
     * @param string $tournamentId
     * @return array
     */
    public function findMatchesInTournament(string $tournamentId) : array
    {
        $query = 'SELECT m.* FROM `matches` m
                  JOIN `match_days` md ON md.id = m.match_day_id
                  WHERE md.tournament_id = ?';
        return $this->getDb()->fetchAll($query, [$tournamentId]);
    }
}