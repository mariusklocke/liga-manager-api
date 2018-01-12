<?php

namespace HexagonalPlayground\Infrastructure\API\Controller;

use HexagonalPlayground\Application\Bus\SingleCommandBus;
use Slim\Http\Response;

abstract class CommandController
{
    /** @var SingleCommandBus */
    protected $commandBus;

    public function __construct(SingleCommandBus $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    /**
     * @param string $message
     * @return Response
     */
    protected function createBadRequestResponse(string $message)
    {
        return (new Response(400))->withJson($message);
    }
}
