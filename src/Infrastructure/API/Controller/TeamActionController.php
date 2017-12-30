<?php
/**
 * TeamActionController.php
 *
 * @author    Marius Klocke <marius.klocke@eventim.de>
 * @copyright Copyright (c) 2017, CTS EVENTIM Solutions GmbH
 */

namespace HexagonalDream\Infrastructure\API\Controller;

use HexagonalDream\Application\Command\CreateTeamCommand;
use HexagonalDream\Application\Command\DeleteTeamCommand;
use HexagonalDream\Application\Exception\NotFoundException;
use HexagonalDream\Application\Handler\CreateTeamHandler;
use HexagonalDream\Application\Handler\DeleteTeamHandler;
use Slim\Http\Request;
use Slim\Http\Response;

class TeamActionController
{
    /** @var CreateTeamHandler */
    private $createTeamHandler;

    /** @var DeleteTeamHandler */
    private $deleteTeamHandler;

    /**
     * @param CreateTeamHandler $createTeamHandler
     * @param DeleteTeamHandler $deleteTeamHandler
     */
    public function __construct(CreateTeamHandler $createTeamHandler, DeleteTeamHandler $deleteTeamHandler)
    {
        $this->createTeamHandler = $createTeamHandler;
        $this->deleteTeamHandler = $deleteTeamHandler;
    }

    /**
     * @param string $teamId
     * @return Response
     */
    public function delete(string $teamId)
    {
        try {
            $this->deleteTeamHandler->handle(new DeleteTeamCommand($teamId));
        } catch (NotFoundException $e) {
            return new Response(404);
        };

        return new Response(204);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function create(Request $request)
    {
        $teamName = $request->getParsedBodyParam('name');
        if (!is_string($teamName) || strlen($teamName) === 0 || strlen($teamName) > 255) {
            return new Response(400);
        }
        $this->createTeamHandler->handle(new CreateTeamCommand($teamName));
        return new Response(204);
    }

    /**
     * @return Response
     */
    public function rename()
    {
        return new Response(404);
    }
}