<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Permission;

use HexagonalPlayground\Domain\User;

class IsAdmin extends Permission
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

    public function check(): void
    {
        if ($this->user->hasRole(User::ROLE_ADMIN)) {
            return;
        }

        $this->fail('This action requires admin rights');
    }
}
