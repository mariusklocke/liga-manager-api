<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL\Loader;

use HexagonalPlayground\Infrastructure\Persistence\Read\TeamRepository;

class BufferedTeamLoader
{
    /** @var TeamRepository */
    private $teamRepository;

    /** @var array */
    private $bySeasonId = [];

    /** @var array */
    private $byUserId = [];

    /** @var array */
    private $byTeamId = [];

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
     * @param string $userId
     */
    public function addUser(string $userId): void
    {
        $this->byUserId[$userId] = null;
    }

    /**
     * @param string $seasonId
     */
    public function addSeason(string $seasonId): void
    {
        $this->bySeasonId[$seasonId] = null;
    }

    /**
     * @param string $teamId
     * @return array|null
     */
    public function getByTeam(string $teamId): ?array
    {
        $teamIds = array_keys($this->byTeamId, null, true);
        foreach ($this->teamRepository->findTeamsById($teamIds) as $id => $team) {
            $this->byTeamId[$id] = $team;
        }
        return $this->byTeamId[$teamId] ?? null;
    }

    /**
     * @param string $userId
     * @return array|null
     */
    public function getByUser(string $userId): ?array
    {
        $userIds = array_keys($this->byUserId, null, true);
        foreach ($this->teamRepository->findTeamsByUserIds($userIds) as $id => $teams) {
            $this->byUserId[$id] = $teams;
        }
        return $this->byUserId[$userId] ?? null;
    }

    /**
     * @param string $seasonId
     * @return array|null
     */
    public function getBySeason(string $seasonId): ?array
    {
        $seasonIds = array_keys($this->bySeasonId, null, true);
        foreach ($this->teamRepository->findTeamsBySeasonIds($seasonIds) as $id => $teams) {
            $this->bySeasonId[$id] = $teams;
        }
        return $this->bySeasonId[$seasonId] ?? null;
    }
}
