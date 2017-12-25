<?php

namespace HexagonalDream\Infrastructure\Persistence;

use HexagonalDream\Application\UuidGeneratorInterface;
use Ramsey\Uuid\Uuid;

class UuidGenerator implements UuidGeneratorInterface
{
    /**
     * {@inheritdoc}
     */
    public function generateUuid() : string
    {
        return Uuid::uuid4()->toString();
    }
}
