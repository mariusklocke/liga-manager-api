<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler\v2;

use HexagonalPlayground\Application\Command\v2\UpdateUserPasswordCommand;
use HexagonalPlayground\Application\Handler\AuthAwareHandler;
use HexagonalPlayground\Application\Security\AuthContext;
use HexagonalPlayground\Application\Security\AuthenticationException;
use HexagonalPlayground\Domain\Event\Event;

class UpdateUserPasswordHandler implements AuthAwareHandler
{
    /**
     * @param UpdateUserPasswordCommand $command
     * @param AuthContext $authContext
     * @return array|Event[]
     */
    public function __invoke(UpdateUserPasswordCommand $command, AuthContext $authContext): array
    {
        $user = $authContext->getUser();

        if (!$user->verifyPassword($command->getOldPassword())) {
            throw new AuthenticationException('Authentication failed');
        }

        $user->setPassword($command->getNewPassword());

        return [];
    }
}
