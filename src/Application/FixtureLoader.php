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

    private static $teamNames = [
        "BSG Rotation Gastfeld",
        "Stümper 02",
        "Zollhaus",
        "Vibrator Moskovskaya",
        "Interruptus Connection",
        "Team Erasmus",
        "Höttges' Erben",
        "Mahatma G Punkt",
        "Blaues Wunder Bremen",
        "Azzurri di Brema",
        "Hansa Gönnung",
        "Hemelinger Helden",
        "Mahatma Gondi",
        "Harpune Poseidon",
        "RB Rockstars",
        "FC Talentfrei",
        "Die elo.minaten"
    ];

    public function __construct(ObjectPersistenceInterface $persistence, UuidGeneratorInterface $uuidGenerator)
    {
        $this->persistence = $persistence;
        $this->uuidGenerator = $uuidGenerator;
    }

    public function __invoke()
    {
        $this->persistence->transactional(function() {
            $pitch = new Pitch($this->uuidGenerator, 'Sportgarten', new GeographicLocation('12.34', '23.45'));
            $this->persist($pitch);
            foreach (static::$teamNames as $teamName) {
                $this->persist(new Team($this->uuidGenerator, $teamName));
            }
            $this->persist(new Season($this->uuidGenerator, 'Winterliga 17/18'));
        });
    }

    private function persist($entity)
    {
        return $this->persistence->persist($entity);
    }
}
