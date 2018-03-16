<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application;

use Generator;
use HexagonalPlayground\Application\Factory\SeasonFactory;
use HexagonalPlayground\Domain\GeographicLocation;
use HexagonalPlayground\Domain\Pitch;
use HexagonalPlayground\Domain\Team;

class FixtureGenerator
{
    /** @var IdGeneratorInterface */
    private $uuidGenerator;

    /** @var SeasonFactory */
    private $seasonFactory;

    public function __construct(IdGeneratorInterface $uuidGenerator, SeasonFactory $seasonFactory)
    {
        $this->uuidGenerator = $uuidGenerator;
        $this->seasonFactory = $seasonFactory;
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
            yield new Team($this->uuidGenerator->generate(), $teamName);
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
                $this->uuidGenerator->generate(),
                'Pitch ' . $color,
                new GeographicLocation(12.34, 23.45)
            );
        }
    }
}