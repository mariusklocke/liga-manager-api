<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\ORM\Repository;

use HexagonalPlayground\Application\Repository\MatchRepositoryInterface;
use HexagonalPlayground\Domain\Match;

class MatchRepository extends EntityRepository implements MatchRepositoryInterface
{
    /**
     * @return string
     */
    protected static function getEntityClass(): string
    {
        return Match::class;
    }
}