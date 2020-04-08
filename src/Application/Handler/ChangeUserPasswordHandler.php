<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\ChangeUserPasswordCommand;
use HexagonalPlayground\Application\Security\AuthContext;

class ChangeUserPasswordHandler implements AuthAwareHandler
{
    /**
     * @param ChangeUserPasswordCommand $command
     * @param AuthContext $authContext
     */
    public function __invoke(ChangeUserPasswordCommand $command, AuthContext $authContext): void
    {
        $authContext->getUser()->setPassword($command->getNewPassword());
    }
}