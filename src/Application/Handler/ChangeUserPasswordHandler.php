<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\ChangeUserPasswordCommand;
use HexagonalPlayground\Application\Security\Authenticator;

class ChangeUserPasswordHandler
{
    /** @var Authenticator */
    private $authenticator;

    /**
     * @param Authenticator $authenticator
     */
    public function __construct(Authenticator $authenticator)
    {
        $this->authenticator = $authenticator;
    }

    /**
     * @param ChangeUserPasswordCommand $command
     */
    public function handle(ChangeUserPasswordCommand $command)
    {
        $user = $this->authenticator->getAuthenticatedUser();
        $user->changePassword($command->getNewPassword());
    }
}