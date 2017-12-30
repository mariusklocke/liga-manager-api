<?php
/**
 * TeamQueryController.php
 *
 * @author    Marius Klocke <marius.klocke@eventim.de>
 * @copyright Copyright (c) 2017, CTS EVENTIM Solutions GmbH
 */

namespace HexagonalDream\Infrastructure\API\Controller;

use HexagonalDream\Application\Repository\TeamRepository;
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
        $team = $this->repository->findTeamById($teamId);
        if (null === $team) {
            return new Response(404);
        }
        return (new Response(200))->withJson($team);
    }

    /**
     * @param string $seasonId
     * @return Response
     */
    public function findTeamsBySeasonId(string $seasonId) : Response
    {
        $teams = $this->repository->findTeamBySeasonId($seasonId);
        return (new Response(200))->withJson($teams);
    }
}