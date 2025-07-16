<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command;

class EndTournamentCommand implements CommandInterface
{
    /** @var string */
    private string $tournamentId;

    /**
     * @param string $tournamentId
     */
    public function __construct(string $tournamentId)
    {
        $this->tournamentId = $tournamentId;
    }

    /**
     * @return string
     */
    public function getTournamentId() : string
    {
        return $this->tournamentId;
    }
}
