<?php

namespace HexagonalDream\Application\Repository;

class SeasonRepository extends AbstractRepository
{
    /**
     * @return array
     */
    public function getAllSeasons()
    {
        return $this->getDb()->fetchAll('SELECT * FROM `seasons`');
    }

    /**
     * @param string $id
     * @return array|null
     */
    public function getSeasonById(string $id)
    {
        $result = $this->getDb()->fetchAll('SELECT * FROM `seasons` WHERE `id` = :id', ['id' => $id]);
        return !empty($result) ? $result[0] : null;
    }
}
