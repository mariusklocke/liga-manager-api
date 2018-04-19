<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Repository;

class PitchRepository extends AbstractRepository
{
    /**
     * @return array
     */
    public function findAllPitches()
    {
        return array_map(function ($row) {
            return $this->reconstructEmbeddedObject($row, 'contact');
        }, $this->getDb()->fetchAll('SELECT * FROM `pitches`'));
    }

    /**
     * @param string $id
     * @return array|null
     */
    public function findPitchById(string $id)
    {
        $pitch = $this->getDb()->fetchFirstRow('SELECT * FROM `pitches` WHERE `id` = ?', [$id]);
        if (null === $pitch) {
            return null;
        }

        return $this->reconstructEmbeddedObject($pitch, 'contact');
    }
}
