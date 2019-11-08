<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain;

use HexagonalPlayground\Domain\Util\Assert;

class GeographicLocation extends ValueObject
{
    /** @var float */
    protected $longitude;

    /** @var float */
    protected $latitude;

    /**
     * @param float $longitude
     * @param float $latitude
     */
    public function __construct(float $longitude, float $latitude)
    {
        Assert::true(abs($longitude) <= 180.0, 'Invalid longitude: Has to be a float between -180.0 and 180.0');
        Assert::true(abs($latitude) <= 90.0, 'Invalid latitude: Has to be a float between -90.0 and 90.0');
        $this->longitude = $longitude;
        $this->latitude = $latitude;
    }
}
