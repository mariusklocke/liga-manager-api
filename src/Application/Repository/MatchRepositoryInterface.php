<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Repository;

use HexagonalPlayground\Application\Exception\NotFoundException;
use HexagonalPlayground\Domain\Match;

interface MatchRepositoryInterface
{
    /**
     * @param string $id
     * @return Match
     * @throws NotFoundException
     */
    public function find($id);

    /**
     * @param Match $match
     */
    public function save($match): void;
}