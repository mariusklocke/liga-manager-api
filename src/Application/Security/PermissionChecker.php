<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Security;

use HexagonalPlayground\Application\Command\CreateUserCommand;
use HexagonalPlayground\Application\Exception\PermissionException;
use HexagonalPlayground\Domain\Match;
use HexagonalPlayground\Domain\User;

class PermissionChecker
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
     * @param Match $match
     * @throws PermissionException
     */
    public function assertCanSubmitResultFor(Match $match): void
    {
        if ($this->user->hasRole(User::ROLE_ADMIN)
            || $this->user->isInTeam($match->getHomeTeam())
            || $this->user->isInTeam($match->getGuestTeam())
        ) {
            return;
        }

        throw new PermissionException($this->user->getEmail() . ' is not permitted to submit results for this match');
    }

    /**
     * @param CreateUserCommand $command
     * @throws PermissionException
     */
    public function assertCanCreateUser(CreateUserCommand $command): void
    {
        if ($this->user->hasRole(User::ROLE_ADMIN)) {
            return;
        }

        if ($this->user->hasRole(User::ROLE_TEAM_MANAGER)) {
            if ($command->getRole() !== User::ROLE_TEAM_MANAGER) {
                throw new PermissionException($this->user->getEmail() . " can only create users with role 'team_manager'");
            }

            $permittedTeamIds = array_flip($this->user->getTeamIds());
            foreach ($command->getTeamIds() as $teamId) {
                if (!isset($permittedTeamIds[$teamId])) {
                    throw new PermissionException(sprintf(
                        $this->user->getEmail() . " is not permitted to create users for team '%s'",
                        $teamId
                    ));
                }
            }
            return;
        }

        throw new PermissionException($this->user->getEmail() . ' is not permitted to create users');
    }
}