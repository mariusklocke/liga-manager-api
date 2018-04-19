<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Controller;

use HexagonalPlayground\Application\Command\CreateTeamCommand;
use HexagonalPlayground\Application\Command\DeleteTeamCommand;
use HexagonalPlayground\Application\Command\UpdateTeamContactCommand;
use HexagonalPlayground\Infrastructure\API\Exception\BadRequestException;
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
            throw new BadRequestException('Invalid team name');
        }
        $teamId = $this->commandBus->execute(new CreateTeamCommand($teamName));
        return (new Response(200))->withJson(['id' => $teamId]);
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

        $this->assertTypeExact('first_name', $firstName, 'string');
        $this->assertTypeExact('last_name', $lastName, 'string');
        $this->assertTypeExact('phone', $phone, 'string');
        $this->assertTypeExact('email', $email, 'string');
        $this->validateEmail($email);

        $this->commandBus->execute(new UpdateTeamContactCommand(
            $teamId,
            $firstName,
            $lastName,
            $phone,
            $email
        ));

        return new Response(204);
    }
}