<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application;

use Generator;
use HexagonalPlayground\Application\Factory\SeasonFactory;
use HexagonalPlayground\Application\Factory\UserFactory;
use HexagonalPlayground\Domain\GeographicLocation;
use HexagonalPlayground\Domain\Pitch;
use HexagonalPlayground\Domain\Team;

class FixtureGenerator
{
    /** @var IdGeneratorInterface */
    private $idGenerator;

    /** @var SeasonFactory */
    private $seasonFactory;

    /** @var UserFactory */
    private $userFactory;

    public function __construct(IdGeneratorInterface $idGenerator, SeasonFactory $seasonFactory, UserFactory $userFactory)
    {
        $this->idGenerator = $idGenerator;
        $this->seasonFactory = $seasonFactory;
        $this->userFactory = $userFactory;
    }

    /**
     * @return Generator
     */
    public function generateSeasons()
    {
        $years = ['17/18', '18/19', '19/20'];
        foreach ($years as $year) {
            yield $this->seasonFactory->createSeason('Season ' . $year);
        }
    }

    /**
     * @return Generator
     */
    public function generateTeams()
    {
        for ($i = 1; $i <= 8; $i++) {
            $teamName = sprintf('Team No. %02d', $i);
            yield new Team($this->idGenerator->generate(), $teamName);
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
                $this->idGenerator->generate(),
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
        for ($i = 1; $i <= 3; $i++) {
            yield $this->userFactory->createUser('user' . $i, 'user' . $i);
        }
    }
}