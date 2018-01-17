<?php

namespace HexagonalPlayground\Application\Factory;

use HexagonalPlayground\Domain\UuidGeneratorInterface;

abstract class EntityFactory
{
    /** @var UuidGeneratorInterface */
    private $uuidGenerator;

    public function __construct(UuidGeneratorInterface $uuidGenerator)
    {
        $this->uuidGenerator = $uuidGenerator;
    }

    /**
     * @return UuidGeneratorInterface
     */
    protected function getIdGenerator()
    {
        return $this->uuidGenerator;
    }
}