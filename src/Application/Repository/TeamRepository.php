<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Repository;

class TeamRepository extends AbstractRepository
{
    /**
     * @return array
     */
    public function findAllTeams()
    {
        return array_map(function($row) {
            return $this->reconstructEmbeddedObject($row, 'contact');
        }, $this->getDb()->fetchAll('SELECT * FROM `teams`'));
    }

    /**
     * @param string $id
     * @return array|null
     */
    public function findTeamById(string $id)
    {
        $team = $this->getDb()->fetchFirstRow('SELECT * FROM `teams` WHERE `id` = ?', [$id]);
        if (null === $team) {
            return null;
        }

        return $this->reconstructEmbeddedObject($team, 'contact');
    }

    /**
     * @param string $seasonId
     * @return array
     */
    public function findTeamsBySeasonId(string $seasonId)
    {
        $query = <<<'SQL'
  SELECT t.* FROM seasons_teams_link st JOIN `teams` t ON t.id = st.team_id WHERE st.season_id = ?
SQL;

        return array_map(function ($row) {
            return $this->reconstructEmbeddedObject($row, 'contact');
        }, $this->getDb()->fetchAll($query, [$seasonId]));
    }
}
