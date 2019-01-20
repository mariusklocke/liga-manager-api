<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read;

class PitchRepository extends AbstractRepository
{
    /**
     * @return array
     */
    public function findAllPitches()
    {
        return array_map([$this, 'hydrate'], $this->getDb()->fetchAll('SELECT * FROM `pitches`'));
    }

    /**
     * @param string $id
     * @return array
     */
    public function findPitchById(string $id): array
    {
        $pitch = $this->getDb()->fetchFirstRow(
            'SELECT * FROM `pitches` WHERE `id` = ?',
            [$id],
            'Cannot find pitch'
        );

        return $this->hydrate($pitch);
    }

    private function hydrate(array $row): array
    {
        return $this->reconstructEmbeddedObject($row, 'contact');
    }
}
