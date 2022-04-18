<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler\v2;

use HexagonalPlayground\Application\Command\v2\UpdateTournamentCommand;
use HexagonalPlayground\Application\Handler\AuthAwareHandler;
use HexagonalPlayground\Application\Repository\TournamentRepositoryInterface;
use HexagonalPlayground\Application\Security\AuthContext;
use HexagonalPlayground\Domain\Tournament;

class UpdateTournamentHandler implements AuthAwareHandler
{
    private TournamentRepositoryInterface $tournamentRepository;

    /**
     * @param TournamentRepositoryInterface $tournamentRepository
     */
    public function __construct(TournamentRepositoryInterface $tournamentRepository)
    {
        $this->tournamentRepository = $tournamentRepository;
    }

    public function __invoke(UpdateTournamentCommand $command, AuthContext $authContext): array
    {
        /** @var Tournament $tournament */
        $tournament = $this->tournamentRepository->find($command->getId());
        $tournament->setName($command->getName());

        $this->tournamentRepository->save($tournament);

        return [];
    }
}
