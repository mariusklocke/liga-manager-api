<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command\v2;

abstract class UpdateCommand
{
    /** @var string */
    protected string $id;

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }
}
