<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Controller;

use HexagonalPlayground\Application\Command\ChangeUserPasswordCommand;
use HexagonalPlayground\Application\Command\CreateUserCommand;
use HexagonalPlayground\Application\Command\SendPasswordResetMailCommand;
use Slim\Http\Request;
use Slim\Http\Response;

class UserCommandController extends CommandController
{
    public function changePassword(Request $request)
    {
        $newPassword = $request->getParsedBodyParam('new_password');
        $this->assertString('new_password', $newPassword);
        $this->commandBus->execute(new ChangeUserPasswordCommand($newPassword, $this->getUserFromRequest($request)));
        return new Response(204);
    }

    public function createUser(Request $request)
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
            $data['teams'],
            $this->getUserFromRequest($request)
        );

        $id = $this->commandBus->execute($command);
        return (new Response(200))->withJson(['id' => $id]);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function sendPasswordResetMail(Request $request): Response
    {
        $email      = $request->getParsedBodyParam('email');
        $targetPath = $request->getParsedBodyParam('target_path');

        $this->assertString('email', $email);
        $this->assertString('target_path', $targetPath);

        $targetUri = $request->getUri()->withPath($targetPath);
        $this->commandBus->execute(new SendPasswordResetMailCommand($email, $targetUri));
        return new Response(204);
    }
}