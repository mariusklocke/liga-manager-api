<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\ORM\Repository;

use HexagonalPlayground\Application\Repository\SeasonRepositoryInterface;
use HexagonalPlayground\Domain\Season;

class SeasonRepository extends EntityRepository implements SeasonRepositoryInterface
{
    /**
     * @return string
     */
    protected static function getEntityClass(): string
    {
        return Season::class;
    }
}