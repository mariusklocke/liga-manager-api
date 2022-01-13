<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\ORM\Repository;

use HexagonalPlayground\Application\Repository\EventRepositoryInterface;
use HexagonalPlayground\Domain\Event\Event;

class EventRepository extends EntityRepository implements EventRepositoryInterface
{
    /**
     * @inheritDoc
     */
    protected static function getEntityClass(): string
    {
        return Event::class;
    }
}
