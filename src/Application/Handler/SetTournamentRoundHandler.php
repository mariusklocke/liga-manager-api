<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\SetTournamentRoundCommand;
use HexagonalPlayground\Application\Factory\MatchFactory;
use HexagonalPlayground\Application\OrmRepositoryInterface;
use HexagonalPlayground\Domain\Team;
use HexagonalPlayground\Domain\Tournament;

class SetTournamentRoundHandler
{
    /** @var MatchFactory */
    private $matchFactory;

    /** @var OrmRepositoryInterface */
    private $tournamentRepository;

    /** @var OrmRepositoryInterface */
    private $matchRepository;

    /** @var OrmRepositoryInterface */
    private $teamRepository;

    /**
     * @param MatchFactory $matchFactory
     * @param OrmRepositoryInterface $tournamentRepository
     * @param OrmRepositoryInterface $matchRepository
     * @param OrmRepositoryInterface $teamRepository
     */
    public function __construct(MatchFactory $matchFactory, OrmRepositoryInterface $tournamentRepository, OrmRepositoryInterface $matchRepository, OrmRepositoryInterface $teamRepository)
    {
        $this->matchFactory = $matchFactory;
        $this->tournamentRepository = $tournamentRepository;
        $this->matchRepository = $matchRepository;
        $this->teamRepository = $teamRepository;
    }

    /**
     * @param SetTournamentRoundCommand $command
     */
    public function handle(SetTournamentRoundCommand $command)
    {
        /** @var Tournament $tournament */
        $tournament = $this->tournamentRepository->find($command->getTournamentId());
        $tournament->clearMatchesForRound($command->getRound());
        foreach ($command->getTeamIdPairs() as $pair) {
            /** @var Team $homeTeam */
            $homeTeam = $this->teamRepository->find($pair[0]);
            /** @var Team $guestTeam */
            $guestTeam = $this->teamRepository->find($pair[1]);

            $match = $this->matchFactory->createMatch($tournament, $command->getRound(), $homeTeam, $guestTeam, $command->getPlannedFor());
            $this->matchRepository->save($match);
            $tournament->addMatch($match);
        }
    }
}