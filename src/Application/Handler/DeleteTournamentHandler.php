<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\DeleteTournamentCommand;
use HexagonalPlayground\Application\Repository\TournamentRepositoryInterface;

class DeleteTournamentHandler
{
    /** @var TournamentRepositoryInterface */
    private $repository;

    /**
     * @param TournamentRepositoryInterface $repository
     */
    public function __construct(TournamentRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param DeleteTournamentCommand $command
     */
    public function __invoke(DeleteTournamentCommand $command)
    {
        $tournament = $this->repository->find($command->getTournamentId());
        $this->repository->delete($tournament);
    }
}