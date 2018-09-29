<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\SetTournamentRoundCommand;
use HexagonalPlayground\Application\Repository\MatchRepositoryInterface;
use HexagonalPlayground\Application\Repository\TeamRepositoryInterface;
use HexagonalPlayground\Application\Repository\TournamentRepositoryInterface;
use HexagonalPlayground\Domain\Match;
use HexagonalPlayground\Domain\MatchDay;
use HexagonalPlayground\Domain\Team;
use HexagonalPlayground\Domain\Tournament;

class SetTournamentRoundHandler
{
    /** @var TournamentRepositoryInterface */
    private $tournamentRepository;

    /** @var MatchRepositoryInterface */
    private $matchRepository;

    /** @var TeamRepositoryInterface */
    private $teamRepository;

    /**
     * @param TournamentRepositoryInterface $tournamentRepository
     * @param MatchRepositoryInterface $matchRepository
     * @param TeamRepositoryInterface $teamRepository
     */
    public function __construct(TournamentRepositoryInterface $tournamentRepository, MatchRepositoryInterface $matchRepository, TeamRepositoryInterface $teamRepository)
    {
        $this->tournamentRepository = $tournamentRepository;
        $this->matchRepository = $matchRepository;
        $this->teamRepository = $teamRepository;
    }

    /**
     * @param SetTournamentRoundCommand $command
     */
    public function __invoke(SetTournamentRoundCommand $command)
    {
        /** @var Tournament $tournament */
        $tournament = $this->tournamentRepository->find($command->getTournamentId());
        $tournament->clearMatchesForRound($command->getRound());
        $round = new MatchDay($tournament, $command->getRound(), $command->getPlannedFor(), $command->getPlannedFor());
        foreach ($command->getTeamIdPairs() as $pair) {
            /** @var Team $homeTeam */
            $homeTeam = $this->teamRepository->find($pair->getHomeTeamId());
            /** @var Team $guestTeam */
            $guestTeam = $this->teamRepository->find($pair->getGuestTeamId());

            $round->addMatch(new Match($round, $homeTeam, $guestTeam, $command->getPlannedFor()));
        }
        $tournament->setMatchDay($round);
        $this->tournamentRepository->save($tournament);
    }
}