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
        return array_map([$this, 'hydrate'], $this->getDb()->fetchAll($this->getBaseQuery()));
    }

    /**
     * @param string $id
     * @return array|null
     */
    public function findTeamById(string $id): ?array
    {
        $query = $this->getBaseQuery() . ' WHERE id = ?';
        $team  = $this->getDb()->fetchFirstRow($query, [$id]);
        if (null === $team) {
            return null;
        }

        return $this->hydrate($team);
    }

    /**
     * @param string $seasonId
     * @return array
     */
    public function findTeamsBySeasonId(string $seasonId): array
    {
        $query = $this->getBaseQuery() . ' JOIN seasons_teams_link ON id = team_id WHERE season_id = ?';
        return array_map([$this, 'hydrate'], $this->getDb()->fetchAll($query, [$seasonId]));
    }

    /**
     * @param string $userId
     * @return array
     */
    public function findTeamsByUserId(string $userId): array
    {
        $query = $this->getBaseQuery() . ' JOIN users_teams_link ON id = team_id WHERE user_id = ?';
        return array_map([$this, 'hydrate'], $this->getDb()->fetchAll($query, [$userId]));
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

    private function hydrate(array $row): array
    {
        return $this->reconstructEmbeddedObject($row, 'contact');
    }
}
