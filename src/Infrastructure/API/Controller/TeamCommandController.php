<?php
/**
 * TeamCommandController.php
 *
 * @author    Marius Klocke <marius.klocke@eventim.de>
 * @copyright Copyright (c) 2017, CTS EVENTIM Solutions GmbH
 */

namespace HexagonalDream\Infrastructure\API\Controller;

use HexagonalDream\Application\Command\CreateTeamCommand;
use HexagonalDream\Application\Command\DeleteTeamCommand;
use HexagonalDream\Application\Exception\NotFoundException;
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
        $this->commandBus->execute(new CreateTeamCommand($teamName));
        return new Response(204);
    }

    /**
     * @return Response
     */
    public function rename() : Response
    {
        return new Response(404);
    }
}