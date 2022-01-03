<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL\Loader;

use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\EqualityFilter;
use HexagonalPlayground\Infrastructure\Persistence\Read\Criteria\Filter;
use HexagonalPlayground\Infrastructure\Persistence\Read\TeamRepository;

class BufferedTeamLoader
{
    /** @var TeamRepository */
    private $teamRepository;

    /** @var array */
    private $bySeasonId = [];

    /** @var array */
    private $byTeamId = [];

    /** @var array */
    private $byUserId = [];

    /**
     * @param TeamRepository $teamRepository
     */
    public function __construct(TeamRepository $teamRepository)
    {
        $this->teamRepository = $teamRepository;
    }

    /**
     * @param string $teamId
     */
    public function addTeam(string $teamId): void
    {
        $this->byTeamId[$teamId] = null;
    }

    /**
     * @param string $seasonId
     */
    public function addSeason(string $seasonId): void
    {
        $this->bySeasonId[$seasonId] = null;
    }

    /**
     * @param string $userId
     */
    public function addUser(string $userId): void
    {
        $this->byUserId[$userId] = null;
    }

    /**
     * @param string $teamId
     * @return array|null
     */
    public function getByTeam(string $teamId): ?array
    {
        $teamIds = array_keys($this->byTeamId, null, true);

        if (count($teamIds)) {
            $filters = [new EqualityFilter('id', Filter::MODE_INCLUDE, $teamIds)];

            foreach ($this->teamRepository->findMany($filters) as $team) {
                $this->byTeamId[$team['id']] = $team;
            }
        }

        return $this->byTeamId[$teamId] ?? null;
    }

    /**
     * @param string $seasonId
     * @return array
     */
    public function getBySeason(string $seasonId): array
    {
        $seasonIds = array_keys($this->bySeasonId, null, true);

        if (count($seasonIds)) {
            $teams = $this->teamRepository->findBySeasonIds($seasonIds);

            foreach ($seasonIds as $id) {
                $this->bySeasonId[$id] = $teams[$id] ?? [];
            }
        }

        return $this->bySeasonId[$seasonId];
    }

    /**
     * @param string $userId
     * @return array
     */
    public function getByUser(string $userId): array
    {
        $userIds = array_keys($this->byUserId, null, true);

        if (count($userIds)) {
            $teams = $this->teamRepository->findByUserIds($userIds);

            foreach ($userIds as $id) {
                $this->byUserId[$id] = $teams[$id] ?? [];
            }
        }

        return $this->byUserId[$userId];
    }
}
