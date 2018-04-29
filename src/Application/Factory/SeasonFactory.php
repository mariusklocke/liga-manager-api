<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Factory;

use HexagonalPlayground\Domain\Season;

class SeasonFactory
{
    /**
     * @param string $name
     * @return Season
     */
    public function createSeason(string $name) : Season
    {
        return new Season($name);
    }
}
