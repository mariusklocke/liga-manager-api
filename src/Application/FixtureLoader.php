<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application;

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
            $teams = [];
            foreach ($this->generator->generateTeams() as $team) {
                $teams[] = $team;
                $this->persistence->persist($team);
            }
            foreach ($this->generator->generateUsers() as $user) {
                foreach ($teams as $team) {
                    $user->addTeam($team);
                }
                $this->persistence->persist($user);
            }
            foreach ($this->generator->generateSeasons() as $season) {
                foreach ($teams as $team) {
                    $season->addTeam($team);
                }
                $this->persistence->persist($season);
            }
        });
    }
}
