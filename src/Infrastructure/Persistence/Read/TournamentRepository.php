<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read;

class TournamentRepository extends AbstractRepository
{
    /**
     * @return array
     */
    public function findAllTournaments() : array
    {
        return array_map([$this, 'hydrate'], $this->getDb()->fetchAll('SELECT * FROM tournaments'));
    }

    /**
     * @param string $id
     * @return array|null
     */
    public function findTournamentById(string $id): ?array
    {
        $row = $this->getDb()->fetchFirstRow('SELECT * FROM tournaments WHERE id = ?', [$id]);

        return $row !== null ? $this->hydrate($row) : null;
    }

    protected function hydrate(array $row): array
    {
        return [
            'id' => $this->hydrator->string($row['id']),
            'name' => $this->hydrator->string($row['name']),
            'rounds' => $this->hydrator->int($row['rounds'])
        ];
    }
}
