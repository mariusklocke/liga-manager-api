<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Factory;

use HexagonalPlayground\Domain\Tournament;

class TournamentFactory
{
    /**
     * @param string $name
     * @return Tournament
     */
    public function createTournament(string $name) : Tournament
    {
        return new Tournament($name);
    }
}