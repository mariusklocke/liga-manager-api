<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Controller;

use HexagonalPlayground\Application\Command\ChangeUserPasswordCommand;
use HexagonalPlayground\Application\Command\CreateUserCommand;
use HexagonalPlayground\Infrastructure\API\Exception\BadRequestException;
use Slim\Http\Request;
use Slim\Http\Response;

class UserCommandController extends CommandController
{
    public function changePassword(Request $request)
    {
        $newPassword = $request->getParsedBodyParam('new_password');
        $this->validatePassword($newPassword);

        $this->commandBus->execute(new ChangeUserPasswordCommand($newPassword));
        return new Response(204);
    }

    public function createUser(Request $request)
    {
        $data = $request->getParsedBody();
        $expected = ['email', 'password', 'first_name', 'last_name', 'role', 'teams'];
        foreach ($expected as $property) {
            if (!isset($data[$property]) || null === $data[$property]) {
                throw new BadRequestException(sprintf("Invalid value for property '%s'", $property));
            }
        }

        $this->validatePassword($data['password']);
        $this->validateEmail($data['email']);
        $command = new CreateUserCommand(
            $data['email'],
            $data['password'],
            $data['first_name'],
            $data['last_name'],
            $data['role'],
            $data['teams']
        );

        $id = $this->commandBus->execute($command);
        return (new Response(200))->withJson(['id' => $id]);
    }

    /**
     * @param $password
     * @throws BadRequestException
     */
    private function validatePassword($password)
    {
        if (!is_string($password) || mb_strlen($password) < 6 || mb_strlen($password) > 255) {
            throw new BadRequestException('Passwords require a length between 6 and 255 characters');
        }
    }

    /**
     * @param $email
     * @throws BadRequestException
     */
    private function validateEmail($email)
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new BadRequestException('Invalid email syntax');
        }
    }
}