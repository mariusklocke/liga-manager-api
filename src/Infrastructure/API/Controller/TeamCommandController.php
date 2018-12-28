<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Controller;

use HexagonalPlayground\Application\Command\CreateTeamCommand;
use HexagonalPlayground\Application\Command\DeleteTeamCommand;
use HexagonalPlayground\Application\Command\RenameTeamCommand;
use HexagonalPlayground\Application\Command\UpdateTeamContactCommand;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;

class TeamCommandController extends CommandController
{
    /**
     * @param Request $request
     * @param string $teamId
     * @return ResponseInterface
     */
    public function delete(Request $request, string $teamId): ResponseInterface
    {
        $command = new DeleteTeamCommand($teamId);
        $this->commandBus->execute($command->withAuthenticatedUser($this->getUserFromRequest($request)));
        return $this->createResponse(204);
    }

    /**
     * @param Request $request
     * @return ResponseInterface
     */
    public function create(Request $request): ResponseInterface
    {
        $command = new CreateTeamCommand($request->getParsedBodyParam('name'));
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
        $command = new UpdateTeamContactCommand(
            $teamId,
            $request->getParsedBodyParam('first_name'),
            $request->getParsedBodyParam('last_name'),
            $request->getParsedBodyParam('phone'),
            $request->getParsedBodyParam('email')
        );
        $this->commandBus->execute($command->withAuthenticatedUser($this->getUserFromRequest($request)));

        return $this->createResponse(204);
    }

    /**
     * @param string $teamId
     * @param Request $request
     * @return ResponseInterface
     */
    public function rename(string $teamId, Request $request): ResponseInterface
    {
        $command = new RenameTeamCommand($teamId, $request->getParsedBodyParam('name'));
        $this->commandBus->execute($command->withAuthenticatedUser($this->getUserFromRequest($request)));

        return $this->createResponse(204);
    }
}