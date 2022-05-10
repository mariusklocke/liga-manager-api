<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\Framework\GraphQL;

use JsonSerializable;

interface NamedOperation extends JsonSerializable
{
    /**
     * @return string
     */
    public function getName(): string;
}
