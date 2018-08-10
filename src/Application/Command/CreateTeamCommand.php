<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command;

use HexagonalPlayground\Domain\User;

class CreateTeamCommand implements CommandInterface
{
    use AuthenticationAware;

    /** @var string */
    private $teamName;

    public function __construct(string $teamName, User $user)
    {
        $this->teamName = $teamName;
        $this->authenticatedUser = $user;
    }

    public function getTeamName() : string
    {
        return $this->teamName;
    }
}
