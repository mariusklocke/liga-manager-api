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
        $newPassword = $request->getParsedBodyParam('new_password');
        $this->assertString('new_password', $newPassword);

        $command = new ChangeUserPasswordCommand($newPassword);
        $this->commandBus->execute($command->withAuthenticatedUser($this->getUserFromRequest($request)));
        return $this->createResponse(204);
    }

    public function createUser(Request $request): ResponseInterface
    {
        $data = $request->getParsedBody();
        $this->assertString('email', $data['email']);
        $this->assertString('password', $data['password']);
        $this->assertString('first_name', $data['first_name']);
        $this->assertString('last_name', $data['last_name']);
        $this->assertString('role', $data['role']);
        $this->assertArray('teams', $data['teams']);
        
        $command = new CreateUserCommand(
            $data['email'],
            $data['password'],
            $data['first_name'],
            $data['last_name'],
            $data['role'],
            $data['teams']
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
        $email      = $request->getParsedBodyParam('email');
        $targetPath = $request->getParsedBodyParam('target_path');

        $this->assertString('email', $email);
        $this->assertString('target_path', $targetPath);

        $targetUri = $request->getUri()->withPath($targetPath);
        $this->commandBus->execute(new SendPasswordResetMailCommand($email, $targetUri));
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
            $request->getParsedBodyParam('team_ids')
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