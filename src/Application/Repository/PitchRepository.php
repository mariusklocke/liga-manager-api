<?php

namespace HexagonalDream\Application\Repository;

class PitchRepository extends AbstractRepository
{
    /**
     * @return array
     */
    public function getAllPitches()
    {
        return $this->getDb()->fetchAll('SELECT * FROM `pitches`');
    }

    /**
     * @param string $id
     * @return array|null
     */
    public function getPitchById(string $id)
    {
        $result = $this->getDb()->fetchAll('SELECT * FROM `pitches` WHERE `id` = :id', ['id' => $id]);
        return !empty($result) ? $result[0] : null;
    }
}
