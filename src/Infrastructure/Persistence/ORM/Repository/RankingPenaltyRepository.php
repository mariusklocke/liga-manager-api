<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\ORM\Repository;

use HexagonalPlayground\Application\Repository\RankingPenaltyRepositoryInterface;
use HexagonalPlayground\Domain\RankingPenalty;

class RankingPenaltyRepository extends EntityRepository implements RankingPenaltyRepositoryInterface
{
    /**
     * @return string
     */
    protected static function getEntityClass(): string
    {
        return RankingPenalty::class;
    }
}
