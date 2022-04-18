<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command\v2;

class UpdateUserCommand extends UpdateCommand implements CommandInterface
{
    /** @var string|null */
    private ?string $email;

    /** @var string|null */
    private ?string $firstName;

    /** @var string|null */
    private ?string $lastName;

    /** @var string|null */
    private ?string $role;

    /** @var string[]|null */
    private ?array $teamIds = null;

    /**
     * @param string $id
     * @param string|null $email
     * @param string|null $firstName
     * @param string|null $lastName
     * @param string|null $role
     * @param string[]|null $teamIds
     */
    public function __construct(string $id, ?string $email, ?string $firstName, ?string $lastName, ?string $role, ?array $teamIds)
    {
        $this->id = $id;
        $this->email = $email;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->role = $role;

        if (null !== $teamIds) {
            $this->teamIds = array_map(function (string $teamId) {
                return $teamId;
            }, $teamIds);
        }
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @return string|null
     */
    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    /**
     * @return string|null
     */
    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    /**
     * @return string|null
     */
    public function getRole(): ?string
    {
        return $this->role;
    }

    /**
     * @return string[]|null
     */
    public function getTeamIds(): ?array
    {
        return $this->teamIds;
    }
}
