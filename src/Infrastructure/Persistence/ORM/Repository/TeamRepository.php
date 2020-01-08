<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\ORM\Repository;

use HexagonalPlayground\Application\Repository\TeamRepositoryInterface;
use HexagonalPlayground\Domain\Team;

class TeamRepository extends EntityRepository implements TeamRepositoryInterface
{
    /**
     * @return string
     */
    protected static function getEntityClass(): string
    {
        return Team::class;
    }
}