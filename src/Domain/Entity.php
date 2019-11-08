<?php declare(strict_types=1);

namespace HexagonalPlayground\Domain;

abstract class Entity
{
    /** @var string */
    protected $id;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param Entity $other
     * @return bool
     */
    public function equals(Entity $other): bool
    {
        return $this->id === $other->id;
    }
}