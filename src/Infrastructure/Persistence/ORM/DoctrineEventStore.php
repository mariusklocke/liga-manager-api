<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\ORM;

use Doctrine\ORM\EntityManagerInterface;
use HexagonalPlayground\Application\EventStoreInterface;
use HexagonalPlayground\Domain\Event\Event;

class DoctrineEventStore implements EventStoreInterface
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Append an event
     *
     * @param Event $event
     */
    public function append(Event $event): void
    {
        $this->entityManager->persist($event);
    }

    /**
     * Find all events
     *
     * @return array
     */
    public function findAll(): array
    {
        return $this->entityManager->getRepository(Event::class)->findAll();
    }

    /**
     * Clear all events
     */
    public function clear(): void
    {
        $query = $this->entityManager->createQueryBuilder();
        $query->delete(Event::class);

        $query->getQuery()->execute();
    }
}