<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain;

use Ramsey\Uuid\Uuid as RamseyUuid;

class Uuid
{
    public static function create()
    {
        return RamseyUuid::uuid4()->toString();
    }
}