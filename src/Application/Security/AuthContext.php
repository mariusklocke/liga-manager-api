<?php declare(strict_types=1);

namespace HexagonalPlayground\Application\Security;

use HexagonalPlayground\Domain\User;

class AuthContext
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
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }
}
