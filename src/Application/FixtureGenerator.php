<?php
/**
 * FixtureGenerator.php
 *
 * @author    Marius Klocke <marius.klocke@eventim.de>
 * @copyright Copyright (c) 2017, CTS EVENTIM Solutions GmbH
 */

namespace HexagonalDream\Application;

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
     * @return Season[]
     */
    public function generateSeasons()
    {
        $seasonList = [];
        $years = ['17/18', '18/19', '19/20'];
        foreach ($years as $year) {
            $seasonList[] = new Season($this->uuidGenerator, 'Season ' . $year);
        }
        return $seasonList;
    }

    /**
     * @return Team[]
     */
    public function generateTeams()
    {
        $teamList = [];
        for ($i = 1; $i <= 8; $i++) {
            $teamName = sprintf('Team No. %02d', $i);
            $teamList[] = new Team($this->uuidGenerator, $teamName);
        }
        return $teamList;
    }

    /**
     * @return Pitch[]
     */
    public function generatePitches()
    {
        $pitchList = [];
        $colors = ['Red', 'Blue'];
        foreach ($colors as $color) {
            $pitchList[] = new Pitch(
                $this->uuidGenerator,
                'Pitch ' . $color,
                new GeographicLocation('12.34', '23.45')
            );
        }
        return $pitchList;
    }
}