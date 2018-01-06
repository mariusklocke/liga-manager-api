<?php

namespace HexagonalDream\Infrastructure\API\Controller;

use HexagonalDream\Application\Command\CreateMatchesForSeasonCommand;
use HexagonalDream\Application\Command\DeleteSeasonCommand;
use HexagonalDream\Application\Command\StartSeasonCommand;
use HexagonalDream\Application\Exception\InvalidStateException;
use Slim\Http\Response;

class SeasonCommandController extends CommandController
{
    /**
     * @param string $seasonId
     * @return Response
     */
    public function createMatches(string $seasonId) : Response
    {
        try {
            $this->commandBus->execute(new CreateMatchesForSeasonCommand($seasonId));
        } catch (InvalidStateException $e) {
            return (new Response(400))->withJson($e->getMessage());
        }
        return new Response(204);
    }

    /**
     * @param string $seasonId
     * @return Response
     */
    public function start(string $seasonId) : Response
    {
        try {
            $this->commandBus->execute(new StartSeasonCommand($seasonId));
        } catch (InvalidStateException $e) {
            return (new Response(400))->withJson($e->getMessage());
        }
        return new Response(204);
    }

    /**
     * @param string $seasonId
     * @return Response
     */
    public function delete(string $seasonId) : Response
    {
        try {
            $this->commandBus->execute(new DeleteSeasonCommand($seasonId));
        } catch (InvalidStateException $e) {
            return (new Response(400))->withJson($e->getMessage());
        }
        return new Response(204);
    }
}
