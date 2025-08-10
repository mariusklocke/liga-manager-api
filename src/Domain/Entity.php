<?php declare(strict_types=1);

namespace HexagonalPlayground\Domain;

use HexagonalPlayground\Domain\Exception\InvalidInputException;
use HexagonalPlayground\Domain\Util\StringUtils;
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
            StringUtils::length($id) > 0 || throw new InvalidInputException('idCannotBeBlank');
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
