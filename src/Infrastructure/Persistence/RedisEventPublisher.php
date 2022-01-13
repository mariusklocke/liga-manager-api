<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence;

use HexagonalPlayground\Domain\Event\Event as DomainEvent;
use Redis;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RedisEventPublisher implements EventSubscriberInterface
{
    /** @var Redis */
    private $redis;

    /**
     * @param Redis $redis
     */
    public function __construct(Redis $redis)
    {
        $this->redis = $redis;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            DomainEvent::class => 'onDomainEvent'
        ];
    }

    /**
     * Handles a DomainEvent by publishing it on a redis channel
     *
     * @param DomainEvent $event
     */
    public function onDomainEvent(DomainEvent $event): void
    {
        $this->redis->publish('events', json_encode($event, JSON_THROW_ON_ERROR));
    }
}
