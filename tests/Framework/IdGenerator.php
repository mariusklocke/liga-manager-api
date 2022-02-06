<?php
declare(strict_types=1);

namespace HexagonalPlayground\Tests\Framework;

use Ramsey\Uuid\Uuid;

class IdGenerator
{
    public static function generate(): string
    {
        return Uuid::uuid4()->toString();
    }
}
