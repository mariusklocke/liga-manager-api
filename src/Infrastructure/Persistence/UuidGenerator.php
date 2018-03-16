<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence;

use HexagonalPlayground\Domain\IdGeneratorInterface;
use Ramsey\Uuid\UuidFactoryInterface;

class UuidGenerator implements IdGeneratorInterface
{
    /** @var UuidFactoryInterface */
    private $factory;

    public function __construct(UuidFactoryInterface $factory)
    {
        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public function generate() : string
    {
        return $this->factory->uuid4()->toString();
    }
}
