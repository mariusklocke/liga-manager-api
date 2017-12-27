<?php

namespace HexagonalDream\Application;

use HexagonalDream\Domain\GeographicLocation;
use HexagonalDream\Domain\Pitch;
use HexagonalDream\Domain\Season;
use HexagonalDream\Domain\Team;

class FixtureLoader
{
    /** @var ObjectPersistenceInterface */
    private $persistence;
    /** @var UuidGeneratorInterface */
    private $uuidGenerator;

    public function __construct(ObjectPersistenceInterface $persistence, UuidGeneratorInterface $uuidGenerator)
    {
        $this->persistence = $persistence;
        $this->uuidGenerator = $uuidGenerator;
    }

    public function __invoke()
    {
        $this->persistence->transactional(function() {
            $pitch = new Pitch($this->uuidGenerator, 'Sportgarten', new GeographicLocation('12.34', '23.45'));
            $this->persistence->persist($pitch);
            $season = new Season($this->uuidGenerator, 'Winterliga 17/18');
            for ($i = 1; $i <= 18; $i++) {
                $teamName = sprintf('Team No. %02d', $i);
                $team = new Team($this->uuidGenerator, $teamName);
                $this->persistence->persist($team);
                $season->addTeam($team);
            }
            $this->persistence->persist($season);
        });
    }
}
