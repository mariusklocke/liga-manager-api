<?php
/**
 * FixtureGenerator.php
 *
 * @author    Marius Klocke <marius.klocke@eventim.de>
 * @copyright Copyright (c) 2017, CTS EVENTIM Solutions GmbH
 */

namespace HexagonalDream\Application;

use Generator;
use HexagonalDream\Domain\GeographicLocation;
use HexagonalDream\Domain\Pitch;
use HexagonalDream\Domain\Season;
use HexagonalDream\Domain\Team;

class FixtureGenerator
{
    /** @var UuidGeneratorInterface */
    private $uuidGenerator;

    public function __construct(UuidGeneratorInterface $uuidGenerator)
    {
        $this->uuidGenerator = $uuidGenerator;
    }

    /**
     * @return Generator
     */
    public function generateSeasons()
    {
        $years = ['17/18', '18/19', '19/20'];
        foreach ($years as $year) {
            yield new Season($this->uuidGenerator, 'Season ' . $year);
        }
    }

    /**
     * @return Generator
     */
    public function generateTeams()
    {
        for ($i = 1; $i <= 8; $i++) {
            $teamName = sprintf('Team No. %02d', $i);
            yield new Team($this->uuidGenerator, $teamName);
        }
    }

    /**
     * @return Generator
     */
    public function generatePitches()
    {
        $colors = ['Red', 'Blue'];
        foreach ($colors as $color) {
            yield new Pitch(
                $this->uuidGenerator,
                'Pitch ' . $color,
                new GeographicLocation('12.34', '23.45')
            );
        }
    }
}