<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\ORM\Repository;

use HexagonalPlayground\Application\Repository\TournamentRepositoryInterface;
use HexagonalPlayground\Domain\Tournament;

class TournamentRepository extends EntityRepository implements TournamentRepositoryInterface
{
    /**
     * @return string
     */
    protected static function getEntityClass(): string
    {
        return Tournament::class;
    }
}