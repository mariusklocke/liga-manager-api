<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain\Value;

use Stringable;

class Uuid extends ValueObject implements Stringable
{
    private string $bytes;

    private function __construct(string $bytes)
    {
        $this->bytes = $bytes;
    }

    public function __toString(): string
    {
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($this->bytes), 4));
    }

    public static function parse(string $id): self
    {
        return new self(hex2bin(str_replace('-', '', $id)));
    }

    public static function generate(): self
    {
        $bytes = random_bytes(16);
        // Set version to 0100
        $bytes[6] = chr(ord($bytes[6]) & 0x0f | 0x40);
        // Set bits 6-7 to 10
        $bytes[8] = chr(ord($bytes[8]) & 0x3f | 0x80);

        return new self($bytes);
    }
}