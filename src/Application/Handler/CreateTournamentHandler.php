<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\CreateTournamentCommand;
use HexagonalPlayground\Application\Factory\TournamentFactory;
use HexagonalPlayground\Application\ObjectPersistenceInterface;

class CreateTournamentHandler
{
    /** @var ObjectPersistenceInterface */
    private $persistence;

    /** @var TournamentFactory */
    private $tournamentFactory;

    /**
     * @param ObjectPersistenceInterface $persistence
     * @param TournamentFactory $tournamentFactory
     */
    public function __construct(ObjectPersistenceInterface $persistence, TournamentFactory $tournamentFactory)
    {
        $this->persistence = $persistence;
        $this->tournamentFactory = $tournamentFactory;
    }

    /**
     * @param CreateTournamentCommand $command
     * @return string
     */
    public function handle(CreateTournamentCommand $command)
    {
        $tournament = $this->tournamentFactory->createTournament($command->getName());
        $this->persistence->persist($tournament);
        return $tournament->getId();
    }
}