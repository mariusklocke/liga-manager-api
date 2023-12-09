<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Logos;

use HexagonalPlayground\Application\Repository\TeamRepositoryInterface;
use HexagonalPlayground\Application\TypeAssert;
use HexagonalPlayground\Domain\Team;

trait TeamFinderTrait
{
    private TeamRepositoryInterface $teamRepository;

    private function findTeam(array $queryParams): Team
    {
        TypeAssert::assertString($queryParams['teamId'], 'teamId');

        /** @var Team $team */
        $team = $this->teamRepository->find($queryParams['teamId']);

        return $team;
    }
}
