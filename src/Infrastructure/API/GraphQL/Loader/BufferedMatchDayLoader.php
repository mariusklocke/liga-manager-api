<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL\Loader;

use HexagonalPlayground\Infrastructure\Persistence\Read\MatchDayRepository;

class BufferedMatchDayLoader
{
    /** @var MatchDayRepository */
    private $matchDayRepository;

    /** @var array */
    private $bySeasonId = [];

    /** @var array */
    private $byTournamentId = [];

    /**
     * @param MatchDayRepository $matchDayRepository
     */
    public function __construct(MatchDayRepository $matchDayRepository)
    {
        $this->matchDayRepository = $matchDayRepository;
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

        if (count($seasonIds)) {
            foreach ($this->matchDayRepository->findBySeasonIds($seasonIds) as $seasonId => $matchDays) {
                $this->bySeasonId[$seasonId] = $matchDays;
            }
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

        if (count($tournamentIds)) {
            foreach ($this->matchDayRepository->findByTournamentIds($tournamentIds) as $tournamentId => $matchDays) {
                $this->byTournamentId[$tournamentId] = $matchDays;
            }
        }

        return $this->byTournamentId[$tournamentId] ?? null;
    }
}
