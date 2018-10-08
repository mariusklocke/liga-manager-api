<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read;

use HexagonalPlayground\Application\Exception\NotFoundException;

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
     * @return array
     */
    public function findPitchById(string $id): array
    {
        $pitch = $this->getDb()->fetchFirstRow('SELECT * FROM `pitches` WHERE `id` = ?', [$id]);
        if (null === $pitch) {
            throw new NotFoundException('Cannot find pitch');
        }

        return $this->reconstructEmbeddedObject($pitch, 'contact');
    }
}
