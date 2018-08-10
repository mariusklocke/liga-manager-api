<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Controller;

use HexagonalPlayground\Application\Command\CreateTeamCommand;
use HexagonalPlayground\Application\Command\DeleteTeamCommand;
use HexagonalPlayground\Application\Command\UpdateTeamContactCommand;
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
        $name = $request->getParsedBodyParam('name');
        $this->assertString('name', $name);
        $id   = $this->commandBus->execute(new CreateTeamCommand($name));
        return (new Response(200))->withJson(['id' => $id]);
    }

    /**
     * @param string $teamId
     * @param Request $request
     * @return Response
     */
    public function updateContact(string $teamId, Request $request) : Response
    {
        $firstName = $request->getParsedBodyParam('first_name');
        $lastName  = $request->getParsedBodyParam('last_name');
        $phone     = $request->getParsedBodyParam('phone');
        $email     = $request->getParsedBodyParam('email');

        $this->assertString('first_name', $firstName);
        $this->assertString('last_name', $lastName);
        $this->assertString('phone', $phone);
        $this->assertString('email', $email);

        $this->commandBus->execute(new UpdateTeamContactCommand(
            $teamId,
            $firstName,
            $lastName,
            $phone,
            $email,
            $this->getUserFromRequest($request)
        ));

        return new Response(204);
    }
}