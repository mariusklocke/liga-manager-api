<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Handler;

use HexagonalPlayground\Application\Command\ChangeUserPasswordCommand;
use HexagonalPlayground\Domain\User;

class ChangeUserPasswordHandler
{
    /** @var User */
    private $user;

    /**
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * @param ChangeUserPasswordCommand $command
     */
    public function handle(ChangeUserPasswordCommand $command)
    {
        $this->user->changePassword($command->getNewPassword());
    }
}