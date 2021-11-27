<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read;

class PitchRepository extends AbstractRepository
{
    protected function getFieldDefinitions(): array
    {
        return [
            'id' => Hydrator::TYPE_STRING,
            'label' => Hydrator::TYPE_STRING,
            'location_longitude' => Hydrator::TYPE_FLOAT,
            'location_latitude' => Hydrator::TYPE_FLOAT,
            'contact' => function (array $row): ?array {
                $contact = [
                    'email' => $row['contact_email'],
                    'first_name' => $row['contact_first_name'],
                    'last_name' => $row['contact_last_name'],
                    'phone' => $row['contact_phone']
                ];

                foreach ($contact as $value) {
                    if ($value !== null) {
                        return $contact;
                    }
                }

                return null;
            }
        ];
    }

    /**
     * @return array
     */
    public function findAllPitches(): array
    {
        return $this->hydrateMany($this->getDb()->fetchAll('SELECT * FROM `pitches`'));
    }

    /**
     * @param string $id
     * @return array|null
     */
    public function findPitchById(string $id): ?array
    {
        $pitch = $this->getDb()->fetchFirstRow('SELECT * FROM `pitches` WHERE `id` = ?', [$id]);

        return $pitch !== null ? $this->hydrateOne($pitch) : null;
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
            $result[$row['id']] = $this->hydrateOne($row);
        }

        return $result;
    }
}
