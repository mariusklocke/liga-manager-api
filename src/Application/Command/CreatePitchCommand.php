<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command;

use HexagonalPlayground\Domain\Value\GeographicLocation;

class CreatePitchCommand implements CommandInterface
{
    use IdAware;

    /** @var string */
    private string $label;

    /** @var GeographicLocation */
    private GeographicLocation $location;

    /**
     * @param string|null $id
     * @param string $label
     * @param float|int $longitude
     * @param float|int $latitude
     */
    public function __construct(?string $id, string $label, float $longitude, float $latitude)
    {
        $this->label = $label;
        $this->location = new GeographicLocation($longitude, $latitude);
        $this->setId($id);
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
