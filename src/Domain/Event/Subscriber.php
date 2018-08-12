<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain\Event;

interface Subscriber
{
    public function handle(Event $event);
}