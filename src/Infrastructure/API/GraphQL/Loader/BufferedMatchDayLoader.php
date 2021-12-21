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
     * @return array|null
     */
    public function getBySeason(string $seasonId): ?array
    {
        $seasonIds = array_keys($this->bySeasonId, null, true);

        if (count($seasonIds)) {
            $filter = new EqualityFilter('season_id', Filter::MODE_INCLUDE, $seasonIds);

            foreach ($this->matchDayRepository->findMany([$filter]) as $matchDay) {
                $this->bySeasonId[$matchDay['season_id']][] = $matchDay;
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
            $filter = new EqualityFilter('tournament_id', Filter::MODE_INCLUDE, $tournamentIds);

            foreach ($this->matchDayRepository->findMany([$filter]) as $matchDay) {
                $this->byTournamentId[$matchDay['tournament_id']][] = $matchDay;
            }
        }

        return $this->byTournamentId[$tournamentId] ?? null;
    }
}
