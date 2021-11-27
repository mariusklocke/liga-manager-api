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
     * @return array|null
     */
    public function findPitchById(string $id): ?array
    {
        $pitch = $this->getDb()->fetchFirstRow('SELECT * FROM `pitches` WHERE `id` = ?', [$id]);
        if (null === $pitch) {
            return null;
        }

        return $this->hydrate($pitch);
    }

    /**
     * @param array $pitchIds
     * @return array
     */
    public function findPitchesById(array $pitchIds): array
    {
        if (empty($pitchIds)) {
            return [];
        }

        $placeholder = $this->getPlaceholders($pitchIds);
        $query = "SELECT * FROM pitches WHERE id IN ($placeholder)";
        $result = [];
        foreach ($this->getDb()->fetchAll($query, $pitchIds) as $row) {
            $result[$row['id']] = $this->hydrate($row);
        }

        return $result;
    }

    protected function hydrate(array $row): array
    {
        return [
            'id' => $this->hydrator->string($row['id']),
            'label' => $this->hydrator->string($row['label']),
            'location_longitude' => $this->hydrator->float($row['location_longitude']),
            'location_latitude' => $this->hydrator->float($row['location_latitude']),
            'contact' => $this->hydrator->contact($row)
        ];
    }
}
