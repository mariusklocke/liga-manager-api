<?php

namespace HexagonalDream\Application\Repository;

class TeamRepository extends AbstractRepository
{
    /**
     * @return array
     */
    public function getAllTeams()
    {
        return $this->getDb()->fetchAll('SELECT * FROM `teams`');
    }

    /**
     * @param string $id
     * @return array|null
     */
    public function getTeamById(string $id)
    {
        $result = $this->getDb()->fetchAll('SELECT * FROM `teams` WHERE `id` = :id', ['id' => $id]);
        return !empty($result) ? $result[0] : null;
    }
}
