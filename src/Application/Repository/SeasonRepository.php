<?php

namespace HexagonalPlayground\Application\Repository;

class SeasonRepository extends AbstractRepository
{
    /**
     * @return array
     */
    public function findAllSeasons()
    {
        return $this->getDb()->fetchAll('SELECT * FROM `seasons`');
    }

    /**
     * @param string $id
     * @return array|null
     */
    public function findSeasonById(string $id)
    {
        return $this->getDb()->fetchFirstRow('SELECT * FROM `seasons` WHERE `id` = ?', [$id]);
    }

    /**
     * @param string $seasonId
     * @return int
     */
    public function countTeamsInSeason(string $seasonId) : int
    {
        $query = 'SELECT COUNT(team_id) FROM seasons_teams_link WHERE season_id = ?';
        return (int) $this->getDb()->fetchSingleColumn($query, [$seasonId]);
    }
}
