<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Factory;

use HexagonalPlayground\Domain\IdGeneratorInterface;

abstract class EntityFactory
{
    /** @var IdGeneratorInterface */
    private $uuidGenerator;

    public function __construct(IdGeneratorInterface $uuidGenerator)
    {
        $this->uuidGenerator = $uuidGenerator;
    }

    /**
     * @return IdGeneratorInterface
     */
    protected function getIdGenerator()
    {
        return $this->uuidGenerator;
    }
}