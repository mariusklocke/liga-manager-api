<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler\v2;

use HexagonalPlayground\Application\Command\v2\CreateTournamentCommand;
use HexagonalPlayground\Application\Handler\AuthAwareHandler;
use HexagonalPlayground\Application\Permission\IsAdmin;
use HexagonalPlayground\Application\Repository\TournamentRepositoryInterface;
use HexagonalPlayground\Application\Security\AuthContext;
use HexagonalPlayground\Domain\Event\Event;
use HexagonalPlayground\Domain\Tournament;

class CreateTournamentHandler implements AuthAwareHandler
{
    /** @var TournamentRepositoryInterface */
    private TournamentRepositoryInterface $tournamentRepository;

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
     * @return array|Event[]
     */
    public function __invoke(CreateTournamentCommand $command, AuthContext $authContext): array
    {
        $events = [];

        $isAdmin = new IsAdmin($authContext->getUser());
        $isAdmin->check();

        $tournament = new Tournament($command->getId(), $command->getName());
        $this->tournamentRepository->save($tournament);

        $events[] = new Event('tournament:created', [
            'tournamentId' => $tournament->getId()
        ]);

        return $events;
    }
}