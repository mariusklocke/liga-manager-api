<?php

namespace HexagonalPlayground\Application\Command;

use HexagonalPlayground\Domain\GeographicLocation;

class CreatePitchCommand implements CommandInterface
{
    /** @var string */
    private $label;

    /** @var GeographicLocation */
    private $location;

    /**
     * @param string             $label
     * @param GeographicLocation $location
     */
    public function __construct(string $label, GeographicLocation $location)
    {
        $this->label = $label;
        $this->location = $location;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @return GeographicLocation
     */
    public function getLocation(): GeographicLocation
    {
        return $this->location;
    }
}