<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Factory;

use HexagonalPlayground\Application\IdGeneratorInterface;

abstract class EntityFactory
{
    /** @var IdGeneratorInterface */
    private $idGenerator;

    public function __construct(IdGeneratorInterface $idGenerator)
    {
        $this->idGenerator = $idGenerator;
    }

    /**
     * @return IdGeneratorInterface
     */
    protected function getIdGenerator()
    {
        return $this->idGenerator;
    }
}