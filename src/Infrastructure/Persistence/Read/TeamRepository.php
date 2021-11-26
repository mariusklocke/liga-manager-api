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
     * @param array $teamIds
     * @return array
     */
    public function findTeamsById(array $teamIds): array
    {
        if (empty($teamIds)) {
            return [];
        }

        $placeholder = $this->getPlaceholders($teamIds);
        $createdAt   = $this->getDateFormat('created_at');
        $query = "SELECT id, name, $createdAt, contact_email, contact_first_name, contact_last_name, contact_phone FROM teams WHERE id IN ($placeholder)";
        $result = [];
        foreach ($this->getDb()->fetchAll($query, $teamIds) as $row) {
            $result[$row['id']] = $this->hydrate($row);
        }

        return $result;
    }

    /**
     * @param array $seasonIds
     * @return array
     */
    public function findTeamsBySeasonIds(array $seasonIds): array
    {
        if (empty($seasonIds)) {
            return [];
        }

        $placeholder = $this->getPlaceholders($seasonIds);
        $createdAt   = $this->getDateFormat('created_at');
        $query = <<<SQL
  SELECT id, name, $createdAt, contact_email, contact_first_name, contact_last_name, contact_phone, season_id
  FROM teams
    JOIN seasons_teams_link ON id=team_id
  WHERE season_id IN ($placeholder)
SQL;
        $result = [];
        foreach ($this->getDb()->fetchAll($query, $seasonIds) as $row) {
            $seasonId = $row['season_id'];
            unset($row['season_id']);
            $result[$seasonId][] = $this->hydrate($row);
        }

        return $result;
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
     * @param array $userIds
     * @return array
     */
    public function findTeamsByUserIds(array $userIds): array
    {
        if (empty($userIds)) {
            return [];
        }

        $placeholder = $this->getPlaceholders($userIds);
        $createdAt   = $this->getDateFormat('created_at');
        $query = <<<SQL
  SELECT id, name, $createdAt, contact_email, contact_first_name, contact_last_name, contact_phone, user_id
  FROM teams
    JOIN users_teams_link ON id=team_id
  WHERE user_id IN ($placeholder)
SQL;
        $result = [];
        foreach ($this->getDb()->fetchAll($query, $userIds) as $row) {
            $userId = $row['user_id'];
            unset($row['user_id']);
            $result[$userId][] = $this->hydrate($row);
        }

        return $result;
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
