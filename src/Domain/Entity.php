<?php declare(strict_types=1);

namespace HexagonalPlayground\Domain;

use HexagonalPlayground\Domain\Util\Assert;

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
     * @param string $id
     */
    protected function setId(string $id): void
    {
        Assert::minLength($id, 1, "An entity id cannot be blank");
        $this->id = $id;
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