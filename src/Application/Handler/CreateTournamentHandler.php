<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\CreateTournamentCommand;
use HexagonalPlayground\Application\Permission\IsAdmin;
use HexagonalPlayground\Application\Repository\TournamentRepositoryInterface;
use HexagonalPlayground\Application\Security\AuthContext;
use HexagonalPlayground\Domain\Tournament;

class CreateTournamentHandler implements AuthAwareHandler
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
     * @param AuthContext $authContext
     */
    public function __invoke(CreateTournamentCommand $command, AuthContext $authContext)
    {
        IsAdmin::check($authContext->getUser());

        $tournament = new Tournament($command->getId(), $command->getName());
        $this->tournamentRepository->save($tournament);
    }
}