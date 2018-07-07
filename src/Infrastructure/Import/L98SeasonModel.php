<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Import;

class L98SeasonModel
{
    /** @var string */
    private $name;

    /**
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}