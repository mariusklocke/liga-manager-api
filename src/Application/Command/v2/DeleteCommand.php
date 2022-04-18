<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command\v2;

abstract class DeleteCommand
{
    /** @var string */
    protected string $id;

    /**
     * @param string $id
     */
    public function __construct(string $id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }
}
