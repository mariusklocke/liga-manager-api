<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Repository;

use HexagonalPlayground\Application\Exception\NotFoundException;
use HexagonalPlayground\Domain\Pitch;

interface PitchRepositoryInterface
{
    /**
     * @param string $id
     * @return Pitch
     * @throws NotFoundException
     */
    public function find($id);

    /**
     * @param Pitch $pitch
     */
    public function save($pitch): void;

    /**
     * @param $pitch
     */
    public function delete($pitch): void;
}