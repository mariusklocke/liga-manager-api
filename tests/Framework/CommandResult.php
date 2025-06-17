<?php
declare(strict_types=1);

namespace HexagonalPlayground\Tests\Framework;

class CommandResult
{
    public function __construct(
        public readonly int $exitCode,
        public readonly string $output,
        public readonly string $errorOutpu
    ) {
        // Using constructor property promotion
    }
}