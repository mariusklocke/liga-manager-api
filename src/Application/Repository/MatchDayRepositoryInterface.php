<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Repository;

use HexagonalPlayground\Application\Exception\NotFoundException;
use HexagonalPlayground\Domain\MatchDay;

interface MatchDayRepositoryInterface
{
    /**
     * @param string $id
     * @return MatchDay
     * @throws NotFoundException
     */
    public function find($id);

    /**
     * @param MatchDay $match
     */
    public function save($match): void;
}