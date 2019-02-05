<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Controller;

use HexagonalPlayground\Application\Command\ChangeUserPasswordCommand;
use HexagonalPlayground\Application\Command\CreateUserCommand;
use HexagonalPlayground\Application\Command\DeleteUserCommand;
use HexagonalPlayground\Application\Command\SendPasswordResetMailCommand;
use HexagonalPlayground\Application\Command\UpdateUserCommand;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Request;

class UserCommandController extends CommandController
{
    public function changePassword(Request $request): ResponseInterface
    {
        $command = new ChangeUserPasswordCommand($request->getParsedBodyParam('new_password'));
        $this->commandBus->execute($command->withAuthenticatedUser($this->getUserFromRequest($request)));
        return $this->createResponse(204);
    }

    public function createUser(Request $request): ResponseInterface
    {
        $command = new CreateUserCommand(
            $request->getParsedBodyParam('email'),
            $request->getParsedBodyParam('password'),
            $request->getParsedBodyParam('first_name'),
            $request->getParsedBodyParam('last_name'),
            $request->getParsedBodyParam('role'),
            $request->getParsedBodyParam('teams')
        );
        $id = $this->commandBus->execute($command->withAuthenticatedUser($this->getUserFromRequest($request)));
        return $this->createResponse(200, ['id' => $id]);
    }

    public function deleteUser(Request $request, string $id): ResponseInterface
    {
        $command = new DeleteUserCommand($id);
        $this->commandBus->execute($command->withAuthenticatedUser($this->getUserFromRequest($request)));
        return $this->createResponse(204);
    }

    /**
     * @param Request $request
     * @return ResponseInterface
     */
    public function sendPasswordResetMail(Request $request): ResponseInterface
    {
        $command = new SendPasswordResetMailCommand(
            $request->getParsedBodyParam('email'),
            $request->getParsedBodyParam('target_path')
        );
        $this->commandBus->execute($command->withBaseUri($request->getUri()));
        return $this->createResponse(204);
    }

    /**
     * @param string $userId
     * @param Request $request
     * @return ResponseInterface
     */
    public function updateUser(string $userId, Request $request): ResponseInterface
    {
        $command = new UpdateUserCommand(
            $this->resolveUserAlias($userId, $request),
            $request->getParsedBodyParam('email'),
            $request->getParsedBodyParam('first_name'),
            $request->getParsedBodyParam('last_name'),
            $request->getParsedBodyParam('role'),
            $request->getParsedBodyParam('teams')
        );
        $this->commandBus->execute($command->withAuthenticatedUser($this->getUserFromRequest($request)));

        return $this->createResponse(204);
    }

    private function resolveUserAlias(string $userId, Request $request): string
    {
        if ($userId === 'me') {
            return $this->getUserFromRequest($request)->getId();
        }
        return $userId;
    }
}