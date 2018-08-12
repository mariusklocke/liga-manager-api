<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain\Event;

final class Publisher
{
    /** @var Subscriber[] */
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
     * @param Event $event
     */
    public function publish(Event $event)
    {
        foreach ($this->subscribers as $subscriber) {
            $subscriber->handle($event);
        }
    }

    /**
     * @param Subscriber $subscriber
     */
    public function addSubscriber(Subscriber $subscriber)
    {
        $this->subscribers[] = $subscriber;
    }
}