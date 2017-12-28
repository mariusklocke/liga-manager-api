<?php

namespace HexagonalDream\Application;

class FixtureLoader
{
    /** @var ObjectPersistenceInterface */
    private $persistence;
    /** @var FixtureGenerator */
    private $generator;

    public function __construct(ObjectPersistenceInterface $persistence, FixtureGenerator $generator)
    {
        $this->persistence = $persistence;
        $this->generator = $generator;
    }

    public function __invoke()
    {
        $this->persistence->transactional(function() {
            foreach ($this->generator->generatePitches() as $pitch) {
                $this->persistence->persist($pitch);
            }
            $teams = $this->generator->generateTeams();
            foreach ($this->generator->generateSeasons() as $season) {
                foreach ($teams as $team) {
                    $season->addTeam($team);
                    $this->persistence->persist($team);
                }
                $this->persistence->persist($season);
            }
        });
    }
}
