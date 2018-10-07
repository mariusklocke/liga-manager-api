<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read;

class SeasonRepository extends AbstractRepository
{
    /**
     * @return array
     */
    public function findAllSeasons()
    {
        return $this->getDb()->fetchAll('SELECT * FROM `seasons`');
    }

    /**
     * @param string $id
     * @return array|null
     */
    public function findSeasonById(string $id)
    {
        return $this->getDb()->fetchFirstRow('SELECT * FROM `seasons` WHERE `id` = ?', [$id]);
    }

    /**
     * @param string $seasonId
     * @return array
     */
    public function findMatchDays(string $seasonId)
    {
        return $this->getDb()->fetchAll('SELECT * FROM `match_days` WHERE season_id = ?', [$seasonId]);
    }
}
