<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL\Loader;

use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\EqualityFilter;
use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\Filter;
use HexagonalPlayground\Infrastructure\Persistence\Read\MatchRepository;

class BufferedMatchLoader
{
    /** @var MatchRepository */
    private MatchRepository $matchRepository;

    /** @var array */
    private array $byMatchDayId = [];

    /** @var array */
    private array $byHomeTeamId = [];

    /** @var array */
    private array $byGuestTeamId = [];

    /** @var array */
    private array $byPitchId = [];

    /**
     * @param MatchRepository $matchRepository
     */
    public function __construct(MatchRepository $matchRepository)
    {
        $this->matchRepository = $matchRepository;
    }

    /**
     * @param string $matchDayId
     */
    public function addMatchDay(string $matchDayId): void
    {
        $this->byMatchDayId[$matchDayId] = null;
    }

    public function addHomeTeam(string $homeTeamId): void
    {
        $this->byHomeTeamId[$homeTeamId] = null;
    }

    public function addGuestTeam(string $guestTeamId): void
    {
        $this->byGuestTeamId[$guestTeamId] = null;
    }

    public function addPitch(string $pitchId): void
    {
        $this->byPitchId[$pitchId] = null;
    }

    /**
     * @param string $matchDayId
     * @return array
     */
    public function getByMatchDay(string $matchDayId): array
    {
        $matchDayIds = array_keys($this->byMatchDayId, null, true);

        if (count($matchDayIds)) {
            $filter = new EqualityFilter(
                'match_day_id',
                Filter::MODE_INCLUDE,
                $matchDayIds
            );

            $matches = $this->matchRepository->findMany([$filter], [], null, 'match_day_id');

            foreach ($matchDayIds as $id) {
                $this->byMatchDayId[$id] = $matches[$id] ?? [];
            }
        }

        return $this->byMatchDayId[$matchDayId];
    }

    public function getByHomeTeam(string $homeTeamId): array
    {
        $homeTeamsId = array_keys($this->byHomeTeamId, null, true);

        if (count($homeTeamsId)) {
            $filter = new EqualityFilter('home_team_id', Filter::MODE_INCLUDE, $homeTeamsId);

            $matches = $this->matchRepository->findMany([$filter], [], null, 'home_team_id');

            foreach ($homeTeamsId as $id) {
                $this->byHomeTeamId[$id] = $matches[$id] ?? [];
            }
        }

        return $this->byHomeTeamId[$homeTeamId];
    }

    public function getByGuestTeam(string $guestTeamId): array
    {
        $guestTeamsId = array_keys($this->byGuestTeamId, null, true);

        if (count($guestTeamsId)) {
            $filter = new EqualityFilter('guest_team_id', Filter::MODE_INCLUDE, $guestTeamsId);

            $matches = $this->matchRepository->findMany([$filter], [], null, 'guest_team_id');

            foreach ($guestTeamsId as $id) {
                $this->byGuestTeamId[$id] = $matches[$id] ?? [];
            }
        }

        return $this->byGuestTeamId[$guestTeamId];
    }

    public function getByPitch(string $pitchId): array
    {
        $pitchIds = array_keys($this->byPitchId, null, true);

        if (count($pitchIds)) {
            $filter = new EqualityFilter('pitch_id', Filter::MODE_INCLUDE, $pitchIds);

            $matches = $this->matchRepository->findMany([$filter], [], null, 'pitch_id');

            foreach ($pitchIds as $id) {
                $this->byPitchId[$id] = $matches[$id] ?? [];
            }
        }

        return $this->byPitchId[$pitchId];
    }
}
