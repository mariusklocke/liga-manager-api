<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL\Loader;

use HexagonalPlayground\Infrastructure\Persistence\Read\AbstractRepository;

class PitchLoader extends AbstractRepository
{
    /**
     * @param array $pitchIds
     * @return array
     */
    public function loadPitchesById(array $pitchIds): array
    {
        if (empty($pitchIds)) {
            return [];
        }

        $placeholder = $this->getPlaceholders($pitchIds);
        $query = "SELECT id, label, location_longitude, location_latitude, contact_email, contact_first_name, contact_last_name, contact_phone FROM pitches WHERE id IN ($placeholder)";
        $result = [];
        foreach ($this->getDb()->fetchAll($query, $pitchIds) as $row) {
            $result[$row['id']] = $this->hydrate($row);
        }

        return $result;
    }

    private function hydrate(array $row): array
    {
        return $this->reconstructEmbeddedObject($row, 'contact');
    }
}