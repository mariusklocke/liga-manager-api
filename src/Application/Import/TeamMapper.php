<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Import;

use HexagonalPlayground\Application\Repository\TeamRepositoryInterface;
use HexagonalPlayground\Domain\Team;
use HexagonalPlayground\Domain\Util\Uuid;

class TeamMapper
{
    /** @var TeamRepositoryInterface */
    private $repository;

    /** @var array */
    private $identityMap;

    /**
     * @param TeamRepositoryInterface $repository
     */
    public function __construct(TeamRepositoryInterface $repository)
    {
        $this->repository  = $repository;
        $this->identityMap = [];
    }

    /**
     * @param L98TeamModel $l98Team
     * @return array|Team[]
     */
    public function getRecommendations(L98TeamModel $l98Team)
    {
        $teams = $this->repository->findAll();
        uasort($teams, function(Team $t1, Team $t2) use ($l98Team) {
            return levenshtein($t1->getName(), $l98Team->getName()) <=> levenshtein($t2->getName(), $l98Team->getName());
        });

        return array_slice($teams, 0, 5);
    }

    public function getDomainModel(L98TeamModel $l98Team)
    {
        return $this->identityMap[$l98Team->getId()] ?? $this->create($l98Team);
    }

    public function map(L98TeamModel $l98Team, Team $domainTeam)
    {
        $this->identityMap[$l98Team->getId()] = $domainTeam;
    }

    private function create(L98TeamModel $l98Team): Team
    {
        $team = new Team(Uuid::create(), $l98Team->getName());
        $this->map($l98Team, $team);
        $this->repository->save($team);
        return $team;
    }
}