<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read;

class TournamentRepository extends AbstractRepository
{
    protected function getFieldDefinitions(): array
    {
        return [
            'id' => Hydrator::TYPE_STRING,
            'name' => Hydrator::TYPE_STRING,
            'rounds' => Hydrator::TYPE_INT
        ];
    }

    /**
     * @return array
     */
    public function findAllTournaments() : array
    {
        return $this->hydrateMany($this->getDb()->fetchAll('SELECT * FROM tournaments'));
    }

    /**
     * @param string $id
     * @return array|null
     */
    public function findTournamentById(string $id): ?array
    {
        $row = $this->getDb()->fetchFirstRow('SELECT * FROM tournaments WHERE id = ?', [$id]);

        return $row !== null ? $this->hydrateOne($row) : null;
    }
}
