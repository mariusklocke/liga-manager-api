<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Controller;

use HexagonalPlayground\Infrastructure\Persistence\Read\TournamentRepository;
use Slim\Http\Response;

class TournamentQueryController
{
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
     * @return Response
     */
    public function findAllTournaments() : Response
    {
        return (new Response(200))->withJson($this->repository->findAllTournaments());
    }

    /**
     * @param string $id
     * @return Response
     */
    public function findTournamentById(string $id) : Response
    {
        return (new Response(200))->withJson($this->repository->findTournamentById($id));
    }

    /**
     * @param string $tournamentId
     * @return Response
     */
    public function findRounds(string $tournamentId): Response
    {
        return (new Response(200))->withJson($this->repository->findRounds($tournamentId));
    }
}