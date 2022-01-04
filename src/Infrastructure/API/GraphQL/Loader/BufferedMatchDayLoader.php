<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL\Loader;

use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\EqualityFilter;
use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\Filter;
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
     * @return array
     */
    public function getBySeason(string $seasonId): array
    {
        $seasonIds = array_keys($this->bySeasonId, null, true);

        if (count($seasonIds)) {
            $filter = new EqualityFilter('season_id', Filter::MODE_INCLUDE, $seasonIds);

            $matchDays = $this->matchDayRepository->findMany([$filter], [], null, 'season_id');

            foreach ($seasonIds as $id) {
                $this->bySeasonId[$id] = $matchDays[$id] ?? [];
            }
        }

        return $this->bySeasonId[$seasonId];
    }

    /**
     * @param string $tournamentId
     * @return array
     */
    public function getByTournament(string $tournamentId): array
    {
        $tournamentIds = array_keys($this->byTournamentId, null ,true);

        if (count($tournamentIds)) {
            $filter = new EqualityFilter('tournament_id', Filter::MODE_INCLUDE, $tournamentIds);

            $matchDays = $this->matchDayRepository->findMany([$filter], [], null, 'tournament_id');

            foreach ($tournamentIds as $id) {
                $this->byTournamentId[$id] = $matchDays[$id] ?? [];
            }
        }

        return $this->byTournamentId[$tournamentId];
    }
}
