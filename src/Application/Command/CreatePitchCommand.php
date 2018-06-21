<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command;

use HexagonalPlayground\Domain\GeographicLocation;

class CreatePitchCommand implements CommandInterface
{
    /** @var string */
    private $label;

    /** @var GeographicLocation */
    private $location;

    /**
     * @param string $label
     * @param float $longitude
     * @param float $latitude
     */
    public function __construct(string $label, float $longitude, float $latitude)
    {
        $this->label = $label;
        $this->location = new GeographicLocation($longitude, $latitude);
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