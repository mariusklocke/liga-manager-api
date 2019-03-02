<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL\Loader;

class BufferedMatchDayLoader
{
    /** @var MatchDayLoader */
    private $matchDayLoader;

    /** @var array */
    private $bySeasonId = [];

    /** @var array */
    private $byTournamentId = [];

    /**
     * @param MatchDayLoader $matchDayLoader
     */
    public function __construct(MatchDayLoader $matchDayLoader)
    {
        $this->matchDayLoader = $matchDayLoader;
    }

    /**
     * @param string $seasonId
     */
    public function addSeason(string $seasonId): void
    {
        $this->bySeasonId[$seasonId] = null;
    }

    /**
     * @param string $tournamentId
     */
    public function addTournament(string $tournamentId): void
    {
        $this->byTournamentId[$tournamentId] = null;
    }

    /**
     * @param string $seasonId
     * @return array|null
     */
    public function getBySeason(string $seasonId): ?array
    {
        $seasonIds = array_keys($this->bySeasonId, null, true);
        foreach ($this->matchDayLoader->loadBySeasonId($seasonIds) as $id => $matchDays) {
            $this->bySeasonId[$id] = $matchDays;
        }
        return $this->bySeasonId[$seasonId] ?? null;
    }

    /**
     * @param string $tournamentId
     * @return array|null
     */
    public function getByTournament(string $tournamentId): ?array
    {
        $tournamentIds = array_keys($this->byTournamentId, null ,true);
        foreach ($this->matchDayLoader->loadByTournamentId($tournamentIds) as $id => $matchDays) {
            $this->byTournamentId[$id] = $matchDays;
        }
        return $this->byTournamentId[$tournamentId] ?? null;
    }
}