<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\LoadFixturesCommand;
use HexagonalPlayground\Application\FixtureGenerator;
use HexagonalPlayground\Application\Repository\PitchRepositoryInterface;
use HexagonalPlayground\Application\Repository\SeasonRepositoryInterface;
use HexagonalPlayground\Application\Repository\TeamRepositoryInterface;
use HexagonalPlayground\Application\Security\UserRepositoryInterface;
use HexagonalPlayground\Domain\User;

class LoadFixturesHandler
{
    /** @var TeamRepositoryInterface */
    private $teamRepository;

    /** @var SeasonRepositoryInterface */
    private $seasonRepository;

    /** @var PitchRepositoryInterface */
    private $pitchRepository;

    /** @var UserRepositoryInterface */
    private $userRepository;

    /**
     * @param TeamRepositoryInterface $teamRepository
     * @param SeasonRepositoryInterface $seasonRepository
     * @param PitchRepositoryInterface $pitchRepository
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(TeamRepositoryInterface $teamRepository, SeasonRepositoryInterface $seasonRepository, PitchRepositoryInterface $pitchRepository, UserRepositoryInterface $userRepository)
    {
        $this->teamRepository   = $teamRepository;
        $this->seasonRepository = $seasonRepository;
        $this->pitchRepository  = $pitchRepository;
        $this->userRepository   = $userRepository;
    }

    /**
     * @param LoadFixturesCommand $command
     */
    public function __invoke(LoadFixturesCommand $command)
    {
        $generator = new FixtureGenerator();
        foreach ($generator->generatePitches() as $pitch) {
            $this->pitchRepository->save($pitch);
        }
        $teams = [];
        foreach ($generator->generateTeams() as $team) {
            $teams[] = $team;
            $this->teamRepository->save($team);
        }
        $i = 0;
        foreach ($generator->generateUsers() as $user) {
            /** @var User $user */
            if ($user->hasRole(User::ROLE_TEAM_MANAGER)) {
                $user->addTeam($teams[$i]);
                $i++;
            }
            $this->userRepository->save($user);
        }
        foreach ($generator->generateSeasons() as $season) {
            foreach ($teams as $team) {
                $season->addTeam($team);
            }
            $this->seasonRepository->save($season);
        }
    }
}