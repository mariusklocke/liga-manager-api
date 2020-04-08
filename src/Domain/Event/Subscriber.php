<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain\Event;

interface Subscriber
{
    /**
     * @param Event $event
     */
    public function handle(Event $event): void;
}