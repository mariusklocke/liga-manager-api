<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\Read\Field;

abstract class Field
{
    /** @var string */
    private string $name;

    /** @var bool */
    private bool $isNullable;

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

    public function withName(string $name): Field
    {
        $clone = clone $this;
        $clone->name = $name;

        return $clone;
    }

    abstract public function validate(mixed $value): void;
}
