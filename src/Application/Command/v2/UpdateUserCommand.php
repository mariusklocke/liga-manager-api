<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command\v2;

class UpdateUserCommand extends UpdateCommand implements CommandInterface
{
    /** @var string */
    private string $email;

    /** @var string */
    private string $firstName;

    /** @var string */
    private string $lastName;

    /** @var string */
    private string $role;

    /** @var string[] */
    private array $teamIds;

    /**
     * @param string $id
     * @param string $email
     * @param string $firstName
     * @param string $lastName
     * @param string $role
     * @param string[] $teamIds
     */
    public function __construct(string $id, string $email, string $firstName, string $lastName, string $role, array $teamIds)
    {
        $this->id = $id;
        $this->email = $email;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->role = $role;
        $this->teamIds = array_map(function (string $teamId) {
            return $teamId;
        }, $teamIds);
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * @return string
     */
    public function getRole(): string
    {
        return $this->role;
    }

    /**
     * @return string[]
     */
    public function getTeamIds(): array
    {
        return $this->teamIds;
    }
}
