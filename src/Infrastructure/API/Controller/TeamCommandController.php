<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Controller;

use HexagonalPlayground\Application\Command\CreateTeamCommand;
use HexagonalPlayground\Application\Command\DeleteTeamCommand;
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
        $this->commandBus->execute(new DeleteTeamCommand($teamId));
        return new Response(204);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function create(Request $request) : Response
    {
        $teamName = $request->getParsedBodyParam('name');
        if (!is_string($teamName) || mb_strlen($teamName) === 0 || mb_strlen($teamName) > 255) {
            return $this->createBadRequestResponse('Invalid team name');
        }
        $teamId = $this->commandBus->execute(new CreateTeamCommand($teamName));
        return (new Response(200))->withJson(['id' => $teamId]);
    }

    /**
     * @return Response
     */
    public function rename() : Response
    {
        return new Response(404);
    }
}