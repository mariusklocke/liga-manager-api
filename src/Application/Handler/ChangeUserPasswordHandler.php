<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\ChangeUserPasswordCommand;
use HexagonalPlayground\Application\Security\AuthContext;

class ChangeUserPasswordHandler
{
    /**
     * @param ChangeUserPasswordCommand $command
     * @param AuthContext $authContext
     */
    public function __invoke(ChangeUserPasswordCommand $command, AuthContext $authContext)
    {
        $authContext->getUser()->setPassword($command->getNewPassword());
    }
}