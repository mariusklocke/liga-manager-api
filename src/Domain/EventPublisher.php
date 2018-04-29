<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain;

final class EventPublisher
{
    /** @var EventSubscriber[] */
    private $subscribers;

    /** @var static */
    private static $instance;

    public static function getInstance(): self
    {
        if (null === static::$instance) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    private function __construct()
    {
        $this->subscribers = [];
    }

    /**
     * @param DomainEvent $event
     */
    public function publish(DomainEvent $event)
    {
        foreach ($this->subscribers as $subscriber) {
            $subscriber->handle($event);
        }
    }

    /**
     * @param EventSubscriber $subscriber
     */
    public function addSubscriber(EventSubscriber $subscriber)
    {
        $this->subscribers[] = $subscriber;
    }
}