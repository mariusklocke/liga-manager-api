<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain;

class GeographicLocation
{
    /** @var float */
    private $longitude;

    /** @var float */
    private $latitude;

    /**
     * @param float $longitude
     * @param float $latitude
     * @throws DomainException
     */
    public function __construct(float $longitude, float $latitude)
    {
        if ($latitude < -90.0 || $latitude > 90.0) {
            throw new DomainException('Invalid latitude: Has to be a float between -90.0 and 90.0');
        }
        if ($longitude < -180.0 || $longitude > 180.0) {
            throw new DomainException('Invalid longitude: Has to be a float between -180.0 and 180.0');
        }
        $this->longitude = $longitude;
        $this->latitude = $latitude;
    }
}
