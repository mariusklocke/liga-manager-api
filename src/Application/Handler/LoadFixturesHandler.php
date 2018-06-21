<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\LoadFixturesCommand;
use HexagonalPlayground\Application\FixtureGenerator;
use HexagonalPlayground\Application\OrmRepositoryInterface;
use HexagonalPlayground\Domain\User;

class LoadFixturesHandler
{
    /** @var OrmRepositoryInterface */
    private $teamRepository;

    /** @var OrmRepositoryInterface */
    private $seasonRepository;

    /** @var OrmRepositoryInterface */
    private $pitchRepository;

    /** @var OrmRepositoryInterface */
    private $userRepository;

    /** @var FixtureGenerator */
    private $generator;

    /**
     * @param OrmRepositoryInterface $teamRepository
     * @param OrmRepositoryInterface $seasonRepository
     * @param OrmRepositoryInterface $pitchRepository
     * @param OrmRepositoryInterface $userRepository
     * @param FixtureGenerator $generator
     */
    public function __construct(OrmRepositoryInterface $teamRepository, OrmRepositoryInterface $seasonRepository, OrmRepositoryInterface $pitchRepository, OrmRepositoryInterface $userRepository, FixtureGenerator $generator)
    {
        $this->teamRepository   = $teamRepository;
        $this->seasonRepository = $seasonRepository;
        $this->pitchRepository  = $pitchRepository;
        $this->userRepository   = $userRepository;
        $this->generator        = $generator;
    }

    /**
     * @param LoadFixturesCommand $command
     */
    public function __invoke(LoadFixturesCommand $command)
    {
        foreach ($this->generator->generatePitches() as $pitch) {
            $this->pitchRepository->save($pitch);
        }
        $teams = [];
        foreach ($this->generator->generateTeams() as $team) {
            $teams[] = $team;
            $this->teamRepository->save($team);
        }
        $i = 0;
        foreach ($this->generator->generateUsers() as $user) {
            /** @var User $user */
            if ($user->hasRole(User::ROLE_TEAM_MANAGER)) {
                $user->addTeam($teams[$i]);
                $i++;
            }
            $this->userRepository->save($user);
        }
        foreach ($this->generator->generateSeasons() as $season) {
            foreach ($teams as $team) {
                $season->addTeam($team);
            }
            $this->seasonRepository->save($season);
        }
    }
}