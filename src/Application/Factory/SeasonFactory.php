<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Factory;

use HexagonalPlayground\Domain\Season;
use HexagonalPlayground\Application\IdGeneratorInterface;

class SeasonFactory extends EntityFactory
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
