<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Metrics;

use RuntimeException;

class Definition
{
    public const TYPE_COUNTER = 'counter';
    public const TYPE_GAUGE = 'gauge';

    public function __construct(public readonly string $name, public readonly string $type, public readonly string $help)
    {
        if (!in_array($type, [self::TYPE_COUNTER, self::TYPE_GAUGE])) {
            throw new RuntimeException("Unsupported metrics type: $type");
        }
    }
}