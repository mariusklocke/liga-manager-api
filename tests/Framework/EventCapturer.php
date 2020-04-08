<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\Framework;

use HexagonalPlayground\Domain\Event\Event;
use HexagonalPlayground\Domain\Event\Publisher;
use HexagonalPlayground\Domain\Event\Subscriber;

class EventCapturer implements Subscriber
{
    /** @var Event[] */
    private $buffer;

    /** @var self */
    private static $instance;

    private function __construct()
    {
        Publisher::getInstance()->addSubscriber($this);
    }

    public static function getInstance(): self
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @param callable $callable
     * @return Event[]
     */
    public function capture(callable $callable): array
    {
        $this->buffer = [];
        $callable();
        return $this->buffer;
    }

    /**
     * @inheritDoc
     */
    public function handle(Event $event): void
    {
        $this->buffer[] = $event;
    }
}