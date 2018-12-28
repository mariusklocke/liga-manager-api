<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command;

use HexagonalPlayground\Application\TypeAssert;

class CreateUserCommand implements CommandInterface
{
    use AuthenticationAware;

    /** @var string */
    private $email;

    /** @var string */
    private $password;

    /** @var string */
    private $firstName;

    /** @var string */
    private $lastName;

    /** @var string */
    private $role;

    /** @var string[] */
    private $teamIds;

    /**
     * @param string $email
     * @param string $password
     * @param string $firstName
     * @param string $lastName
     * @param string $role
     * @param string[] $teamIds
     */
    public function __construct($email, $password, $firstName, $lastName, $role, $teamIds)
    {
        TypeAssert::assertString($email, 'email');
        TypeAssert::assertString($password, 'password');
        TypeAssert::assertString($firstName, 'firstName');
        TypeAssert::assertString($lastName, 'lastName');
        TypeAssert::assertString($role, 'role');
        TypeAssert::assertArray($teamIds, 'teamIds');

        $this->email = $email;
        $this->password = $password;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->role = $role;
        $this->teamIds = [];
        foreach ($teamIds as $index => $teamId) {
            TypeAssert::assertString($teamId, 'teamIds[' . $index . ']');
            $this->teamIds[] = $teamId;
        }
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
    public function getPassword(): string
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