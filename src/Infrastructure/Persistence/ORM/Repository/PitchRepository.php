<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\ORM\Repository;

use HexagonalPlayground\Application\Repository\PitchRepositoryInterface;
use HexagonalPlayground\Domain\Pitch;

class PitchRepository extends EntityRepository implements PitchRepositoryInterface
{
    /**
     * @return string
     */
    protected static function getEntityClass(): string
    {
        return Pitch::class;
    }
}