<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command;

use HexagonalPlayground\Domain\User;

class ChangeUserPasswordCommand implements CommandInterface
{
    use AuthenticationAware;

    /** @var string */
    private $newPassword;

    public function __construct(string $newPassword, User $authenticatedUser)
    {
        $this->newPassword = $newPassword;
        $this->authenticatedUser = $authenticatedUser;
    }

    /**
     * @return string
     */
    public function getNewPassword(): string
    {
        return $this->newPassword;
    }
}