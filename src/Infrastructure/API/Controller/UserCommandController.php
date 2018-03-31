<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Controller;

use HexagonalPlayground\Application\Command\ChangeUserPasswordCommand;
use HexagonalPlayground\Infrastructure\API\Exception\BadRequestException;
use Slim\Http\Request;
use Slim\Http\Response;

class UserCommandController extends CommandController
{
    public function changePassword(Request $request)
    {
        $newPassword = $request->getParsedBodyParam('new_password');
        if (!is_string($newPassword) || mb_strlen($newPassword) < 6 || mb_strlen($newPassword) > 255) {
            throw new BadRequestException('New password requires a length between 6 and 255 characters');
        }

        $this->commandBus->execute(new ChangeUserPasswordCommand($newPassword));
        return new Response(204);
    }
}