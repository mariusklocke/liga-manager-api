<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\ORM\Repository;

use HexagonalPlayground\Application\Repository\MatchDayRepositoryInterface;
use HexagonalPlayground\Domain\MatchDay;

class MatchDayRepository extends EntityRepository implements MatchDayRepositoryInterface
{
    /**
     * @inheritDoc
     */
    protected static function getEntityClass(): string
    {
        return MatchDay::class;
    }
}