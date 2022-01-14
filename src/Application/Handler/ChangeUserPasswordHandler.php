<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\ChangeUserPasswordCommand;
use HexagonalPlayground\Application\Security\AuthContext;
use HexagonalPlayground\Domain\Event\Event;

class ChangeUserPasswordHandler implements AuthAwareHandler
{
    /**
     * @param ChangeUserPasswordCommand $command
     * @param AuthContext $authContext
     * @return array|Event[]
     */
    public function __invoke(ChangeUserPasswordCommand $command, AuthContext $authContext): array
    {
        $authContext->getUser()->setPassword($command->getNewPassword());

        return [];
    }
}
