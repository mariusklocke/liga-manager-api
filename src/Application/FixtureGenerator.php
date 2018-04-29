<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application;

use Generator;
use HexagonalPlayground\Domain\GeographicLocation;
use HexagonalPlayground\Domain\Pitch;
use HexagonalPlayground\Domain\Season;
use HexagonalPlayground\Domain\Team;
use HexagonalPlayground\Domain\User;

class FixtureGenerator
{
    /**
     * @return Generator
     */
    public function generateSeasons()
    {
        $years = ['17/18', '18/19', '19/20'];
        foreach ($years as $year) {
            yield new Season('Season ' . $year);
        }
    }

    /**
     * @return Generator
     */
    public function generateTeams()
    {
        for ($i = 1; $i <= 8; $i++) {
            $teamName = sprintf('Team No. %02d', $i);
            yield new Team($teamName);
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
                'Pitch ' . $color,
                new GeographicLocation(12.34, 23.45)
            );
        }
    }

    /**
     * @return Generator
     */
    public function generateUsers()
    {
        $admin = new User('admin', 'admin', 'admin', 'admin');
        $admin->setRole(User::ROLE_ADMIN);
        yield $admin;

        for ($i = 1; $i <= 8; $i++) {
            $teamManager = new User('user' . $i, 'user' . $i, 'admin', 'admin');
            $teamManager->setRole(User::ROLE_TEAM_MANAGER);
            yield $teamManager;
        }
    }
}