<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL\Loader;

use HexagonalPlayground\Infrastructure\Persistence\Read\AbstractRepository;

class TeamLoader extends AbstractRepository
{
    /**
     * @param array $teamIds
     * @return array
     */
    public function loadTeamsById(array $teamIds): array
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
    public function loadTeamsBySeasonId(array $seasonIds): array
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
     * @param array $userIds
     * @return array
     */
    public function loadTeamsByUserId(array $userIds): array
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

    private function hydrate(array $row): array
    {
        return $this->reconstructEmbeddedObject($row, 'contact');
    }
}