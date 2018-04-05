<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\CreateTournamentCommand;
use HexagonalPlayground\Application\Factory\TournamentFactory;
use HexagonalPlayground\Application\OrmRepositoryInterface;

class CreateTournamentHandler
{
    /** @var TournamentFactory */
    private $tournamentFactory;

    /** @var OrmRepositoryInterface */
    private $tournamentRepository;

    /**
     * @param TournamentFactory $tournamentFactory
     * @param OrmRepositoryInterface $tournamentRepository
     */
    public function __construct(TournamentFactory $tournamentFactory, OrmRepositoryInterface $tournamentRepository)
    {
        $this->tournamentFactory = $tournamentFactory;
        $this->tournamentRepository = $tournamentRepository;
    }

    /**
     * @param CreateTournamentCommand $command
     * @return string
     */
    public function handle(CreateTournamentCommand $command)
    {
        $tournament = $this->tournamentFactory->createTournament($command->getName());
        $this->tournamentRepository->save($tournament);
        return $tournament->getId();
    }
}