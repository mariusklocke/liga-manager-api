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
        return $this->getDb()->fetchAll('SELECT * FROM tournaments');
    }

    /**
     * @param string $id
     * @return array|null
     */
    public function findTournamentById(string $id): ?array
    {
        return $this->getDb()->fetchFirstRow('SELECT * FROM tournaments WHERE id = ?', [$id]);
    }

    /**
     * @param string $tournamentId
     * @return array
     */
    public function findRounds(string $tournamentId): array
    {
        return $this->getDb()->fetchAll(
            'SELECT * FROM `match_days` WHERE tournament_id = ? ORDER BY number ASC',
            [$tournamentId]
        );
    }
}