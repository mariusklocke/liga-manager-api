<?php

namespace HexagonalPlayground\Infrastructure\API\Controller;

use HexagonalPlayground\Application\Command\CreateTeamCommand;
use HexagonalPlayground\Application\Command\DeleteTeamCommand;
use HexagonalPlayground\Application\Exception\NotFoundException;
use Slim\Http\Request;
use Slim\Http\Response;

class TeamCommandController extends CommandController
{
    /**
     * @param string $teamId
     * @return Response
     */
    public function delete(string $teamId) : Response
    {
        try {
            $this->commandBus->execute(new DeleteTeamCommand($teamId));
        } catch (NotFoundException $e) {
            return new Response(404);
        };

        return new Response(204);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function create(Request $request) : Response
    {
        $teamName = $request->getParsedBodyParam('name');
        if (!is_string($teamName) || strlen($teamName) === 0 || strlen($teamName) > 255) {
            return new Response(400);
        }
        $teamId = $this->commandBus->execute(new CreateTeamCommand($teamName));
        return (new Response(204))->withHeader('X-Object-Id', $teamId);
    }

    /**
     * @return Response
     */
    public function rename() : Response
    {
        return new Response(404);
    }
}