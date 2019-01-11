<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read;

class TeamRepository extends AbstractRepository
{
    /**
     * @return array
     */
    public function findAllTeams()
    {
        return array_map(function($row) {
            return $this->reconstructEmbeddedObject($row, 'contact');
        }, $this->getDb()->fetchAll($this->getBaseQuery()));
    }

    /**
     * @param string $id
     * @return array
     */
    public function findTeamById(string $id): array
    {
        $query = $this->getBaseQuery() . ' WHERE id = ?';
        $team  = $this->getDb()->fetchFirstRow($query, [$id], 'Cannot find team');

        return $this->reconstructEmbeddedObject($team, 'contact');
    }

    /**
     * @param string $seasonId
     * @return array
     */
    public function findTeamsBySeasonId(string $seasonId)
    {
        $query = $this->getBaseQuery() . ' JOIN seasons_teams_link ON id = team_id WHERE season_id = ?';
        return array_map(function ($row) {
            return $this->reconstructEmbeddedObject($row, 'contact');
        }, $this->getDb()->fetchAll($query, [$seasonId]));
    }

    /**
     * @return string
     */
    private function getBaseQuery(): string
    {
        $createdAt = $this->getDateFormat('created_at');
        $query = <<<SQL
  SELECT id, name, $createdAt, contact_email, contact_first_name, contact_last_name, contact_phone FROM teams
SQL;
        return $query;
    }
}
