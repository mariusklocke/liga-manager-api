<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain\Util;

use Ramsey\Uuid\Uuid as RamseyUuid;

class Uuid
{
    public static function create()
    {
        return RamseyUuid::uuid4()->toString();
    }
}