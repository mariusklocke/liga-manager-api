<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain\Value;

use HexagonalPlayground\Domain\Exception\InvalidInputException;

class GeographicLocation extends ValueObject
{
    /** @var float */
    protected float $longitude;

    /** @var float */
    protected float $latitude;

    /**
     * @param float $longitude
     * @param float $latitude
     */
    public function __construct(float $longitude, float $latitude)
    {
        abs($longitude) <= 180.0 || throw new InvalidInputException('geoLocationLongitudeInvalid');
        abs($latitude) <= 90.0 || throw new InvalidInputException('geoLocationLatitudeInvalid');
        $this->longitude = $longitude;
        $this->latitude = $latitude;
    }

    /**
     * @return float
     */
    public function getLongitude(): float
    {
        return $this->longitude;
    }

    /**
     * @return float
     */
    public function getLatitude(): float
    {
        return $this->latitude;
    }
}
