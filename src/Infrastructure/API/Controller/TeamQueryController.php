<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Controller;

use HexagonalPlayground\Infrastructure\Persistence\Read\TeamRepository;
use Slim\Http\Response;

class TeamQueryController
{
    /** @var TeamRepository */
    private $repository;

    public function __construct(TeamRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @return Response
     */
    public function findAllTeams() : Response
    {
        return (new Response(200))->withJson($this->repository->findAllTeams());
    }

    /**
     * @param string $teamId
     * @return Response
     */
    public function findTeamById(string $teamId) : Response
    {
        return (new Response(200))->withJson($this->repository->findTeamById($teamId));
    }

    /**
     * @param string $seasonId
     * @return Response
     */
    public function findTeamsBySeasonId(string $seasonId) : Response
    {
        return (new Response(200))->withJson($this->repository->findTeamsBySeasonId($seasonId));
    }
}