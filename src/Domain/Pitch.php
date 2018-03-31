<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain;

class Pitch
{
    /** @var string */
    private $id;

    /** @var string */
    private $label;

    /** @var GeographicLocation */
    private $location;

    public function __construct(string $id, string $label, GeographicLocation $location)
    {
        $this->id = $id;
        $this->label = $label;
        $this->location = $location;
    }

    public function copy(string $id)
    {
        $clone = clone $this;
        $clone->id = $id;
        return $clone;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    private function __clone()
    {
        $this->id = null;
    }
}
