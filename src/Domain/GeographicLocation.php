<?php

namespace HexagonalPlayground\Domain;

class GeographicLocation
{
    /** @var float */
    private $longitude;

    /** @var float */
    private $latitude;

    public function __construct(float $longitude, float $latitude)
    {
        $this->longitude = $longitude;
        $this->latitude = $latitude;
    }
}
