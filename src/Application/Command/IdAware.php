<?php declare(strict_types=1);

namespace HexagonalPlayground\Application\Command;

use HexagonalPlayground\Domain\Value\Uuid;

trait IdAware
{
    /** @var string */
    private string $id;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    private function setId(?string $id): void
    {
        if (null === $id) {
            $this->id = (string)Uuid::generate();
            return;
        }

        $this->id = $id;
    }
}
