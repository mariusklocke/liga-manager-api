<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\CreateTournamentCommand;
use HexagonalPlayground\Application\Permission\IsAdmin;
use HexagonalPlayground\Application\Repository\TournamentRepositoryInterface;
use HexagonalPlayground\Domain\Tournament;

class CreateTournamentHandler
{
    /** @var TournamentRepositoryInterface */
    private $tournamentRepository;

    /**
     * @param TournamentRepositoryInterface $tournamentRepository
     */
    public function __construct(TournamentRepositoryInterface $tournamentRepository)
    {
        $this->tournamentRepository = $tournamentRepository;
    }

    /**
     * @param CreateTournamentCommand $command
     */
    public function __invoke(CreateTournamentCommand $command)
    {
        IsAdmin::check($command->getAuthenticatedUser());

        $tournament = new Tournament($command->getId(), $command->getName());
        $this->tournamentRepository->save($tournament);
    }
}