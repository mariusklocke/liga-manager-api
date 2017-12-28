<?php
/**
 * TeamQueryController.php
 *
 * @author    Marius Klocke <marius.klocke@eventim.de>
 * @copyright Copyright (c) 2017, CTS EVENTIM Solutions GmbH
 */

namespace HexagonalDream\Infrastructure\API\Controller;

use HexagonalDream\Application\Repository\TeamRepository;
use Slim\Http\Request;
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
     * @param Request  $request
     * @param Response $response
     * @return Response
     */
    public function findAllTeams(Request $request, Response $response) : Response
    {
        return $response->withJson($this->repository->findAllTeams());
    }

    /**
     * @param Request  $request
     * @param Response $response
     * @return Response
     */
    public function findTeamById(Request $request, Response $response) : Response
    {
        $teamId = $request->getQueryParam('id');
        if (!is_string($teamId) || strlen($teamId) === 0) {
            return $response->withStatus(404);
        }
        $team = $this->repository->findTeamById($teamId);
        if (null === $team) {
            return $response->withStatus(404);
        }
        return $response->withJson($team);
    }

    /**
     * @param Request  $request
     * @param Response $response
     * @return Response
     */
    public function findTeamsBySeasonId(Request $request, Response $response) : Response
    {
        $seasonId = $request->getQueryParam('seasonId');
        if (!is_string($seasonId) || strlen($seasonId) === 0) {
            return $response->withJson([]);
        }
        return $response->withJson($this->repository->findTeamBySeasonId($seasonId));
    }
}