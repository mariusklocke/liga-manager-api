<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Controller;

use HexagonalPlayground\Infrastructure\API\ResponseFactoryTrait;
use HexagonalPlayground\Infrastructure\Persistence\Read\TournamentRepository;
use Psr\Http\Message\ResponseInterface;

class TournamentQueryController
{
    use ResponseFactoryTrait;

    /** @var TournamentRepository */
    private $repository;

    /**
     * @param TournamentRepository $repository
     */
    public function __construct(TournamentRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return ResponseInterface
     */
    public function findAllTournaments(): ResponseInterface
    {
        return $this->createResponse(200, $this->repository->findAllTournaments());
    }

    /**
     * @param string $id
     * @return ResponseInterface
     */
    public function findTournamentById(string $id): ResponseInterface
    {
        return $this->createResponse(200, $this->repository->findTournamentById($id));
    }

    /**
     * @param string $tournamentId
     * @return ResponseInterface
     */
    public function findRounds(string $tournamentId): ResponseInterface
    {
        return $this->createResponse(200, $this->repository->findRounds($tournamentId));
    }
}