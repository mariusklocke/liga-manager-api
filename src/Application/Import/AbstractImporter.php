<?php declare(strict_types=1);

namespace HexagonalPlayground\Application\Import;

use HexagonalPlayground\Domain\Util\Uuid;

abstract class AbstractImporter
{
    protected function generateId(): string
    {
        return Uuid::create();
    }
}