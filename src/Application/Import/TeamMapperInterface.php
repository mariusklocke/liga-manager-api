<?php declare(strict_types=1);

namespace HexagonalPlayground\Application\Import;

interface TeamMapperInterface
{
    /**
     * Returns the internal id for a team name
     *
     * @param string $name
     * @return string|null
     */
    public function map(string $name): ?string;
}
