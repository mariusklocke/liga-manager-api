<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use HexagonalPlayground\Domain\Util\Assert;

class User implements \JsonSerializable
{
    const ROLE_TEAM_MANAGER = 'team_manager';
    const ROLE_ADMIN = 'admin';

    /** @var string */
    private $id;

    /** @var string */
    private $email;

    /** @var string */
    private $password;

    /** @var string */
    private $firstName;

    /** @var string */
    private $lastName;

    /** @var DateTimeImmutable|null */
    private $lastPasswordChange;

    /** @var Collection|Team[] */
    private $teams;

    /** @var string */
    private $role;

    /**
     * @param string $id
     * @param string $email
     * @param string $password
     * @param string $firstName
     * @param string $lastName
     * @param string $role
     */
    public function __construct(
        string $id,
        string $email,
        string $password,
        string $firstName,
        string $lastName,
        string $role = self::ROLE_TEAM_MANAGER
    ) {
        Assert::minLength($id, 1, "A user's id cannot be blank");
        $this->id = $id;
        $this->setEmail($email);
        $this->setPassword($password);
        $this->setFirstName($firstName);
        $this->setLastName($lastName);
        $this->setRole($role);
        $this->teams = new ArrayCollection();
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
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $password
     */
    public function setPassword(string $password): void
    {
        Assert::minLength($password, 6, 'Password does not reach the minimum length of 6 characters');
        Assert::maxLength($password, 255, 'Password exceeds maximum length of 255 characters');
        $this->password = password_hash($password, PASSWORD_BCRYPT);
        $this->lastPasswordChange = new DateTimeImmutable();
    }

    /**
     * @param DateTimeImmutable $since
     * @return bool
     */
    public function hasPasswordChangedSince(DateTimeImmutable $since): bool
    {
        if (null === $this->lastPasswordChange) {
            return false;
        }

        return ($this->lastPasswordChange > $since);
    }

    /**
     * @param string $password
     * @return bool
     */
    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password);
    }

    /**
     * @param Team $team
     */
    public function addTeam(Team $team): void
    {
        if (!$this->teams->contains($team)) {
            $this->teams[$team->getId()] = $team;
        }
    }

    /**
     * @param Team $team
     * @return bool
     */
    public function isInTeam(Team $team): bool
    {
        return $this->teams->contains($team);
    }

    /**
     * @param string $role
     * @return bool
     */
    public function hasRole(string $role): bool
    {
        return $role === $this->role;
    }

    /**
     * @param string $role
     */
    public function setRole(string $role): void
    {
        Assert::oneOf($role, [self::ROLE_ADMIN, self::ROLE_TEAM_MANAGER], 'Invalid role value. Valid: [%s], Got: %s');
        $this->role = $role;
    }

    /**
     * @return string
     */
    public function getRole(): string
    {
        return $this->role;
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
    public function getFullName(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    /**
     * @param string $lastName
     */
    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email): void
    {
        Assert::emailAddress($email, 'Invalid email address for user');
        $this->email = $email;
    }

    /**
     * @param User $other
     * @return bool
     */
    public function equals(User $other): bool
    {
        return $this->id === $other->id;
    }

    /**
     * Clears all team associations
     */
    public function clearTeams(): void
    {
        $this->teams->clear();
    }

    /**
     * @param bool $withTeamIds
     * @return array
     */
    public function jsonSerialize($withTeamIds = true)
    {
        $data = [
            'id' => $this->id,
            'email' => $this->email,
            'role' => $this->role,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName
        ];
        if ($withTeamIds) {
            $data['teams'] = $this->teams->getKeys();
        }

        return $data;
    }
}