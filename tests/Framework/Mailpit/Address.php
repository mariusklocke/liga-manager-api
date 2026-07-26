<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\Framework\Mailpit;

class Address
{
    public function __construct(
        public readonly string $address,
        public readonly string $name
    ) {}
}