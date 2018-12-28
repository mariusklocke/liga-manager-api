<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command;

use HexagonalPlayground\Application\TypeAssert;
use HexagonalPlayground\Domain\GeographicLocation;

class CreatePitchCommand implements CommandInterface
{
    use AuthenticationAware;

    /** @var string */
    private $label;

    /** @var GeographicLocation */
    private $location;

    /**
     * @param string $label
     * @param float|int $longitude
     * @param float|int $latitude
     */
    public function __construct($label, $longitude, $latitude)
    {
        TypeAssert::assertString($label, 'label');
        TypeAssert::assertNumber($longitude, 'longitude');
        TypeAssert::assertNumber($latitude, 'latitude');
        $this->label = $label;
        $this->location = new GeographicLocation((float)$longitude, (float)$latitude);
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