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
        return $this->getDb()->fetchAll('SELECT * FROM `teams`');
    }

    /**
     * @param string $id
     * @return array|null
     */
    public function findTeamById(string $id)
    {
        return $this->getDb()->fetchFirstRow('SELECT * FROM `teams` WHERE `id` = :id', ['id' => $id]);
    }

    /**
     * @param string $seasonId
     * @return array
     */
    public function findTeamsBySeasonId(string $seasonId)
    {
        $query = <<<'SQL'
  SELECT t.* FROM seasons_teams_link st JOIN `teams` t ON t.id = st.team_id WHERE st.season_id = :seasonId
SQL;

        return $this->getDb()->fetchAll($query, ['seasonId' => $seasonId]);
    }
}
