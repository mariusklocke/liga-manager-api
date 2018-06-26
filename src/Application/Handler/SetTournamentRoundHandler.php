<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\SetTournamentRoundCommand;
use HexagonalPlayground\Application\Repository\MatchRepositoryInterface;
use HexagonalPlayground\Application\Repository\TeamRepositoryInterface;
use HexagonalPlayground\Application\Repository\TournamentRepositoryInterface;
use HexagonalPlayground\Domain\MatchFactory;
use HexagonalPlayground\Domain\Team;
use HexagonalPlayground\Domain\Tournament;

class SetTournamentRoundHandler
{
    /** @var MatchFactory */
    private $matchFactory;

    /** @var TournamentRepositoryInterface */
    private $tournamentRepository;

    /** @var MatchRepositoryInterface */
    private $matchRepository;

    /** @var TeamRepositoryInterface */
    private $teamRepository;

    /**
     * @param MatchFactory $matchFactory
     * @param TournamentRepositoryInterface $tournamentRepository
     * @param MatchRepositoryInterface $matchRepository
     * @param TeamRepositoryInterface $teamRepository
     */
    public function __construct(MatchFactory $matchFactory, TournamentRepositoryInterface $tournamentRepository, MatchRepositoryInterface $matchRepository, TeamRepositoryInterface $teamRepository)
    {
        $this->matchFactory = $matchFactory;
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
        foreach ($command->getTeamIdPairs() as $pair) {
            /** @var Team $homeTeam */
            $homeTeam = $this->teamRepository->find($pair->getHomeTeamId());
            /** @var Team $guestTeam */
            $guestTeam = $this->teamRepository->find($pair->getGuestTeamId());

            $match = $this->matchFactory->createMatch($tournament, $command->getRound(), $homeTeam, $guestTeam, $command->getPlannedFor());
            $this->matchRepository->save($match);
            $tournament->addMatch($match);
        }
    }
}