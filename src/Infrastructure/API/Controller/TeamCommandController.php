<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Controller;

use HexagonalPlayground\Application\Command\CreateTeamCommand;
use HexagonalPlayground\Application\Command\DeleteTeamCommand;
use HexagonalPlayground\Application\Command\UpdateTeamContactCommand;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;

class TeamCommandController extends CommandController
{
    /**
     * @param string $teamId
     * @return ResponseInterface
     */
    public function delete(string $teamId): ResponseInterface
    {
        $this->commandBus->execute(new DeleteTeamCommand($teamId));
        return $this->createResponse(204);
    }

    /**
     * @param Request $request
     * @return ResponseInterface
     */
    public function create(Request $request): ResponseInterface
    {
        $name = $request->getParsedBodyParam('name');
        $this->assertString('name', $name);

        $command = new CreateTeamCommand($name);
        $id = $this->commandBus->execute($command->withAuthenticatedUser($this->getUserFromRequest($request)));
        return $this->createResponse(200, ['id' => $id]);
    }

    /**
     * @param string $teamId
     * @param Request $request
     * @return ResponseInterface
     */
    public function updateContact(string $teamId, Request $request): ResponseInterface
    {
        $firstName = $request->getParsedBodyParam('first_name');
        $lastName  = $request->getParsedBodyParam('last_name');
        $phone     = $request->getParsedBodyParam('phone');
        $email     = $request->getParsedBodyParam('email');

        $this->assertString('first_name', $firstName);
        $this->assertString('last_name', $lastName);
        $this->assertString('phone', $phone);
        $this->assertString('email', $email);

        $command = new UpdateTeamContactCommand($teamId, $firstName, $lastName, $phone, $email);
        $this->commandBus->execute($command->withAuthenticatedUser($this->getUserFromRequest($request)));

        return $this->createResponse(204);
    }
}