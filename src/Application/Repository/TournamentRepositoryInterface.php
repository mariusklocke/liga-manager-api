<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Repository;

use HexagonalPlayground\Application\Exception\NotFoundException;
use HexagonalPlayground\Domain\Tournament;

interface TournamentRepositoryInterface
{
    /**
     * @param string $id
     * @return Tournament
     * @throws NotFoundException
     */
    public function find($id);

    /**
     * @param Tournament $tournament
     */
    public function save($tournament): void;

    /**
     * @param Tournament $tournament
     */
    public function delete($tournament): void;
}