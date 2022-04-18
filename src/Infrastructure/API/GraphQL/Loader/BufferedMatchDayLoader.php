<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL\Loader;

use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\EqualityFilter;
use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\Filter;
use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\Sorting;
use HexagonalPlayground\Infrastructure\Persistence\Read\MatchDayRepository;

class BufferedMatchDayLoader
{
    /** @var MatchDayRepository */
    private MatchDayRepository $matchDayRepository;

    /** @var array */
    private array $byMatchDayId = [];

    /** @var array */
    private array $bySeasonId = [];

    /** @var array */
    private array $byTournamentId = [];

    /**
     * @param MatchDayRepository $matchDayRepository
     */
    public function __construct(MatchDayRepository $matchDayRepository)
    {
        $this->matchDayRepository = $matchDayRepository;
    }

    public function addMatchDay(string $matchDayId): void
    {
        $this->byMatchDayId[$matchDayId] = null;
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

    public function getByMatchDay(string $matchDayId): ?array
    {
        $matchDayIds = array_keys($this->byMatchDayId, null, true);

        if (count($matchDayIds)) {
            $filter = new EqualityFilter(
                'id',
                Filter::MODE_INCLUDE,
                $matchDayIds
            );

            $sorting = new Sorting(
                'number',
                Sorting::DIRECTION_ASCENDING
            );

            $matchDays = $this->matchDayRepository->findMany([$filter], [$sorting]);

            foreach ($matchDays as $matchDay) {
                $this->byMatchDayId[$matchDay['id']] = $matchDay;
            }
        }

        return $this->byMatchDayId[$matchDayId] ?? null;
    }

    /**
     * @param string $seasonId
     * @return array
     */
    public function getBySeason(string $seasonId): array
    {
        $seasonIds = array_keys($this->bySeasonId, null, true);

        if (count($seasonIds)) {
            $filter = new EqualityFilter(
                'season_id',
                Filter::MODE_INCLUDE,
                $seasonIds
            );

            $sorting = new Sorting(
                'number',
                Sorting::DIRECTION_ASCENDING
            );

            $matchDays = $this->matchDayRepository->findMany(
                [$filter],
                [$sorting],
                null,
                'season_id'
            );

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
            $filter = new EqualityFilter(
                'tournament_id',
                Filter::MODE_INCLUDE,
                $tournamentIds
            );

            $sorting = new Sorting(
                'number',
                Sorting::DIRECTION_ASCENDING
            );

            $matchDays = $this->matchDayRepository->findMany(
                [$filter],
                [$sorting],
                null,
                'tournament_id'
            );

            foreach ($tournamentIds as $id) {
                $this->byTournamentId[$id] = $matchDays[$id] ?? [];
            }
        }

        return $this->byTournamentId[$tournamentId];
    }
}
