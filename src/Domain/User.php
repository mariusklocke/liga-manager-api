<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class User
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
     */
    public function __construct(
        string $id,
        string $email,
        string $password,
        string $firstName,
        string $lastName
    ) {
        $this->id = $id;
        $this->email = $email;
        $this->password = $this->hashPassword($password);
        $this->lastPasswordChange = null;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->teams = new ArrayCollection();
        $this->role = self::ROLE_TEAM_MANAGER;
    }

    /**
     * @param string $password
     * @return string
     */
    private function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT);
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
    public function changePassword(string $password): void
    {
        $this->password = $this->hashPassword($password);
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
            $this->teams[] = $team;
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
        $this->assertValidRole($role);
        $this->role = $role;
    }

    /**
     * @param string $role
     */
    private function assertValidRole(string $role): void
    {
        $valid = [self::ROLE_ADMIN, self::ROLE_TEAM_MANAGER];
        if (!in_array($role, $valid)) {
            throw new DomainException(sprintf(
                'Unexpected role value. Valid: [%s], Got: %s',
                implode(',', $valid),
                $role
            ));
        }
    }

    public function getTeamIds(): array
    {
        return $this->teams->getKeys();
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
}