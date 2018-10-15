<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command;

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
    public function __construct(string $teamId, string $firstName, string $lastName, string $phone, string $email)
    {
        $this->teamId = $teamId;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->phone = $phone;
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getTeamId(): string
    {
        return $this->teamId;
    }
}