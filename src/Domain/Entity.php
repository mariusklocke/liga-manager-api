<?php declare(strict_types=1);

namespace HexagonalPlayground\Domain;

use HexagonalPlayground\Domain\Util\Assert;
use HexagonalPlayground\Domain\Util\Uuid;

abstract class Entity
{
    /** @var string */
    protected string $id;

    /**
     * @param string|null $id
     */
    public function __construct(?string $id = null)
    {
        if ($id !== null) {
            Assert::minLength($id, 1, "An entity id cannot be blank");
            $this->id = $id;
        } else {
            $this->id = Uuid::create();
        }
    }

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
