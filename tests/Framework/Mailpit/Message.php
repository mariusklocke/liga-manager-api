<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\Framework\Mailpit;

class Message
{
    public function __construct(
        public readonly string $id,
        public readonly Address $from,
        public readonly array $to,
        public readonly string $subject,
        public readonly string $text,
        public readonly string $html
    ) {}
}