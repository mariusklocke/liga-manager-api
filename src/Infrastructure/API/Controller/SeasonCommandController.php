<?php

namespace HexagonalDream\Infrastructure\API\Controller;

use HexagonalDream\Application\Command\StartSeasonCommand;
use Slim\Http\Response;

class SeasonCommandController extends CommandController
{
    public function start(string $seasonId) : Response
    {
        $this->commandBus->execute(new StartSeasonCommand($seasonId));
        return new Response(204);
    }
}
