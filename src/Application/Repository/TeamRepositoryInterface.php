<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Repository;

use HexagonalPlayground\Application\Exception\NotFoundException;
use HexagonalPlayground\Domain\Team;

interface TeamRepositoryInterface
{
    /**
     * @return Team[]
     */
    public function findAll();

    /**
     * @param string $id
     * @return Team
     * @throws NotFoundException
     */
    public function find($id);

    /**
     * @param Team $team
     */
    public function save($team): void;

    /**
     * @param Team $team
     */
    public function delete($team): void;
}