<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read;

class SeasonRepository extends AbstractRepository
{
    protected function getFieldDefinitions(): array
    {
        return [
            'id' => Hydrator::TYPE_STRING,
            'name' => Hydrator::TYPE_STRING,
            'state' => Hydrator::TYPE_STRING,
            'match_day_count' => Hydrator::TYPE_INT,
            'team_count' => Hydrator::TYPE_INT
        ];
    }

    /**
     * @return array
     */
    public function findAllSeasons(): array
    {
        return $this->hydrateMany($this->getDb()->fetchAll('SELECT * FROM `seasons`'));
    }

    /**
     * @param string $id
     * @return array|null
     */
    public function findSeasonById(string $id): ?array
    {
        $season = $this->getDb()->fetchFirstRow('SELECT * FROM `seasons` WHERE `id` = ?', [$id]);

        return $season !== null ? $this->hydrateOne($season) : null;
    }
}
