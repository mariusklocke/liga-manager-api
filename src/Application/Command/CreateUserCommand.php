<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command;

class CreateUserCommand implements CommandInterface
{
    use IdAware;

    /** @var string */
    private string $email;

    /** @var string|null */
    private ?string $password;

    /** @var string */
    private string $firstName;

    /** @var string */
    private string $lastName;

    /** @var string */
    private string $role;

    /** @var string[] */
    private array $teamIds;

    /**
     * @param string|null $id
     * @param string $email
     * @param string|null $password
     * @param string $firstName
     * @param string $lastName
     * @param string $role
     * @param string[] $teamIds
     */
    public function __construct(
        ?string $id,
        string $email,
        ?string $password,
        string $firstName,
        string $lastName,
        string $role,
        array $teamIds
    ) {
        $this->setId($id);
        $this->email = $email;
        $this->password = $password;
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
     * @return string|null
     */
    public function getPassword(): ?string
    {
        return $this->password;
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
