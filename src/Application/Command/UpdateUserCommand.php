<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command;

class UpdateUserCommand implements CommandInterface
{
    /** @var string */
    private string $userId;

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

    /** @var string|null */
    private ?string $locale;

    /**
     * @param string $userId
     * @param string|null $email
     * @param string|null $firstName
     * @param string|null $lastName
     * @param string|null $role
     * @param string[]|null $teamIds
     * @param string|null $locale
     */
    public function __construct(string $userId, ?string $email, ?string $firstName, ?string $lastName, ?string $role, ?array $teamIds, ?string $locale)
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
        $this->locale = $locale;
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

    /**
     * @return string|null
     */
    public function getLocale(): ?string
    {
        return $this->locale;
    }
}
