<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read\Field;

abstract class Field
{
    /** @var string */
    private $name;

    /** @var bool */
    private $isNullable;

    public function __construct(string $name, bool $isNullable)
    {
        $this->name = $name;
        $this->isNullable = $isNullable;
    }

    public function getName(): string
    {
        return $this->name;
    }

    abstract public function hydrate(array $row);

    public function isNullable(): bool
    {
        return $this->isNullable;
    }
}
