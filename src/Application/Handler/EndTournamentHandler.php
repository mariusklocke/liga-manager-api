<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\EndTournamentCommand;
use HexagonalPlayground\Application\Repository\TournamentRepositoryInterface;
use HexagonalPlayground\Application\Security\AuthContext;
use HexagonalPlayground\Domain\Event\Event;
use HexagonalPlayground\Domain\Tournament;

class EndSeasonHandler implements AuthAwareHandler
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
     * @param EndTournamentCommand $command
     * @param AuthContext $authContext
     * @return array|Event[]
     */
    public function __invoke(EndTournamentCommand $command, AuthContext $authContext): array
    {
        $events = [];

        $authContext->getUser()->assertIsAdmin();

        /** @var Tournament $tournament */
        $tournament = $this->tournamentRepository->find($command->getTournamentId());
        $tournament->end();

        $events[] = new Event('tournament:ended', [
            'tournamentId' => $tournament->getId()
        ]);

        return $events;
    }
}
