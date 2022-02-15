<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Permission;

use HexagonalPlayground\Domain\Team;
use HexagonalPlayground\Domain\User;

class CanManageTeam extends Permission
{
    /** @var Team */
    private Team $team;

    /** @var User */
    private User $user;

    /**
     * @param Team $team
     * @param User $user
     */
    public function __construct(Team $team, User $user)
    {
        $this->team = $team;
        $this->user = $user;
    }

    public function check(): void
    {
        if ($this->user->hasRole(User::ROLE_ADMIN) || $this->user->isInTeam($this->team)) {
            return;
        }

        $this->fail('User is not permitted to manage this team');
    }
}