<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read;

use HexagonalPlayground\Application\Exception\NotFoundException;

class SeasonRepository extends AbstractRepository
{
    /**
     * @return array
     */
    public function findAllSeasons(): array
    {
        return $this->getDb()->fetchAll('SELECT * FROM `seasons`');
    }

    /**
     * @param string $id
     * @return array
     */
    public function findSeasonById(string $id): array
    {
        $season = $this->getDb()->fetchFirstRow('SELECT * FROM `seasons` WHERE `id` = ?', [$id]);
        if (null === $season) {
            throw new NotFoundException('Cannot find season');
        }
        return $season;
    }

    /**
     * @param string $seasonId
     * @return array
     */
    public function findMatchDays(string $seasonId): array
    {
        return $this->getDb()->fetchAll(
            'SELECT * FROM `match_days` WHERE season_id = ? ORDER BY number ASC',
            [$seasonId]
        );
    }

    /**
     * @param string $seasonId
     * @return array
     */
    public function findRanking(string $seasonId): array
    {
        $ranking = $this->getDb()->fetchFirstRow(
            'SELECT * FROM rankings WHERE season_id = ?',
            [$seasonId]
        );
        if (null === $ranking) {
            throw new NotFoundException('Cannot find ranking');
        }

        $ranking['positions'] = $this->findRankingPositions($seasonId);
        $ranking['penalties'] = $this->findRankingPenalties($seasonId);
        return $ranking;
    }

    /**
     * @param string $seasonId
     * @return array
     */
    private function findRankingPositions(string $seasonId): array
    {
        return $this->getDb()->fetchAll(
            'SELECT * FROM ranking_positions WHERE season_id = ? ORDER BY sort_index ASC',
            [$seasonId]
        );
    }

    /**
     * @param string $seasonId
     * @return array
     */
    private function findRankingPenalties(string $seasonId): array
    {
        return $this->getDb()->fetchAll(
            'SELECT * FROM ranking_penalties WHERE season_id = ? ORDER BY created_at ASC',
            [$seasonId]
        );
    }
}
