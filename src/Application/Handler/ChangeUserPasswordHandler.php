<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\ChangeUserPasswordCommand;

class ChangeUserPasswordHandler
{
    /**
     * @param ChangeUserPasswordCommand $command
     */
    public function __invoke(ChangeUserPasswordCommand $command)
    {
        $command->getAuthenticatedUser()->changePassword($command->getNewPassword());
    }
}