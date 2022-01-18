<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command;

class UpdateUserCommand implements CommandInterface
{
    /** @var string */
    private $userId;

    /** @var string|null */
    private $email;

    /** @var string|null */
    private $firstName;

    /** @var string|null */
    private $lastName;

    /** @var string|null */
    private $role;

    /** @var string[]|null */
    private $teamIds;

    /**
     * @param string $userId
     * @param string|null $email
     * @param string|null $firstName
     * @param string|null $lastName
     * @param string|null $role
     * @param string[]|null $teamIds
     */
    public function __construct(string $userId, ?string $email, ?string $firstName, ?string $lastName, ?string $role, ?array $teamIds)
    {
        $this->userId = $userId;
        $this->email  = $email;
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
     * @return string
     */
    public function getUserId(): string
    {
        return $this->userId;
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
