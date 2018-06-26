<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Repository;

use HexagonalPlayground\Application\Exception\NotFoundException;
use HexagonalPlayground\Domain\Season;

interface SeasonRepositoryInterface
{
    /**
     * @param string $id
     * @return Season
     * @throws NotFoundException
     */
    public function find($id);

    /**
     * @param Season $season
     */
    public function save($season): void;

    /**
     * @param Season $season
     */
    public function delete($season): void;
}