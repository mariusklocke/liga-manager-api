<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command;

use HexagonalPlayground\Domain\User;

class UpdateTeamContactCommand extends UpdateContactCommand
{
    use AuthenticationAware;

    /** @var string */
    private $teamId;

    /**
     * @param string $teamId
     * @param string $firstName
     * @param string $lastName
     * @param string $phone
     * @param string $email
     */
    public function __construct(string $teamId, string $firstName, string $lastName, string $phone, string $email, User $user)
    {
        $this->teamId = $teamId;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->phone = $phone;
        $this->email = $email;
        $this->authenticatedUser = $user;
    }

    /**
     * @return string
     */
    public function getTeamId(): string
    {
        return $this->teamId;
    }
}