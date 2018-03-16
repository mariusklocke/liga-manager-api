<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\SetTournamentRoundCommand;
use HexagonalPlayground\Application\Factory\MatchFactory;
use HexagonalPlayground\Application\ObjectPersistenceInterface;
use HexagonalPlayground\Domain\Team;
use HexagonalPlayground\Domain\Tournament;

class SetTournamentRoundHandler
{
    /** @var ObjectPersistenceInterface */
    private $persistence;

    /** @var MatchFactory */
    private $matchFactory;

    /**
     * @param ObjectPersistenceInterface $persistence
     * @param MatchFactory $matchFactory
     */
    public function __construct(ObjectPersistenceInterface $persistence, MatchFactory $matchFactory)
    {
        $this->persistence  = $persistence;
        $this->matchFactory = $matchFactory;
    }

    /**
     * @param SetTournamentRoundCommand $command
     */
    public function handle(SetTournamentRoundCommand $command)
    {
        /** @var Tournament $tournament */
        $tournament = $this->persistence->find(Tournament::class, $command->getTournamentId());
        $tournament->clearMatchesForRound($command->getRound());
        foreach ($command->getTeamIdPairs() as $pair) {
            /** @var Team $homeTeam */
            $homeTeam = $this->persistence->find(Team::class, $pair[0]);
            /** @var Team $guestTeam */
            $guestTeam = $this->persistence->find(Team::class, $pair[1]);

            $match = $this->matchFactory->createMatch($tournament, $command->getRound(), $homeTeam, $guestTeam);
            $this->persistence->persist($match);
            $tournament->addMatch($match);
        }
    }
}