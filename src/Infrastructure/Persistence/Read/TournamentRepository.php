<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read;

use HexagonalPlayground\Application\Exception\NotFoundException;

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
     * @return array
     */
    public function findTournamentById(string $id): array
    {
        $tournament = $this->getDb()->fetchFirstRow('SELECT * FROM tournaments WHERE id = ?', [$id]);
        if (null === $tournament) {
            throw new NotFoundException('Cannot find tournament');
        }

        return $tournament;
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