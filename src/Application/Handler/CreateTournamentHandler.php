<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\CreateTournamentCommand;
use HexagonalPlayground\Application\OrmRepositoryInterface;
use HexagonalPlayground\Domain\Tournament;

class CreateTournamentHandler
{
    /** @var OrmRepositoryInterface */
    private $tournamentRepository;

    /**
     * @param OrmRepositoryInterface $tournamentRepository
     */
    public function __construct(OrmRepositoryInterface $tournamentRepository)
    {
        $this->tournamentRepository = $tournamentRepository;
    }

    /**
     * @param CreateTournamentCommand $command
     * @return string
     */
    public function handle(CreateTournamentCommand $command)
    {
        $tournament = new Tournament($command->getName());
        $this->tournamentRepository->save($tournament);
        return $tournament->getId();
    }
}