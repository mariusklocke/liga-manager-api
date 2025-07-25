<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use HexagonalPlayground\Domain\Exception\InvalidInputException;
use HexagonalPlayground\Domain\Exception\PermissionException;
use HexagonalPlayground\Domain\Util\Assert;
use HexagonalPlayground\Domain\Util\StringUtils;

class User extends Entity
{
    const ROLE_TEAM_MANAGER = 'team_manager';
    const ROLE_ADMIN = 'admin';

    /** @var string */
    private string $email;

    /** @var string|null */
    private ?string $password = null;

    /** @var string */
    private string $firstName;

    /** @var string */
    private string $lastName;

    /** @var DateTimeImmutable|null */
    private ?DateTimeImmutable $lastPasswordChange = null;

    /** @var DateTimeImmutable|null */
    private ?DateTimeImmutable $lastTokenInvalidation = null;

    /** @var Collection */
    private Collection $teams;

    /** @var string */
    private string $role;

    /** @var string|null */
    private ?string $locale;

    /**
     * @param string $id
     * @param string $email
     * @param string|null $password
     * @param string $firstName
     * @param string $lastName
     * @param string $role
     * @param string|null $locale
     */
    public function __construct(
        string $id,
        string $email,
        ?string $password,
        string $firstName,
        string $lastName,
        string $role = self::ROLE_TEAM_MANAGER,
        ?string $locale = null
    ) {
        parent::__construct($id);
        $this->setEmail($email);
        $this->setPassword($password);
        $this->setFirstName($firstName);
        $this->setLastName($lastName);
        $this->setRole($role);
        $this->setLocale($locale);
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
     * @param string|null $password
     */
    public function setPassword(?string $password): void
    {
        if (null !== $password) {
            Assert::true(
                StringUtils::length($password) >= 6,
                InvalidInputException::class,
                'userPasswordTooShort',
                [6]
            );
            Assert::true(
                StringUtils::length($password) <= 255,
                InvalidInputException::class,
                'userPasswordTooLong',
                [255]
            );
            $this->password = password_hash($password, PASSWORD_BCRYPT);
        } else {
            $this->password = null;
        }
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
        if ($this->password === null) {
            return false;
        }
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
        Assert::true(
            in_array($role, self::getRoles()),
            InvalidInputException::class,
            'userRoleUnknown'
        );
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
        Assert::true(
            StringUtils::isValidEmailAddress($email),
            InvalidInputException::class,
            'userEmailInvalid'
        );
        $this->email = $email;
    }

    /**
     * Clears all team associations
     */
    public function clearTeams(): void
    {
        $this->teams->clear();
    }

    /**
     * Invalidates all access token
     */
    public function invalidateAccessTokens(): void
    {
        $this->lastTokenInvalidation = new DateTimeImmutable();
    }

    /**
     * @param DateTimeImmutable $since
     * @return bool
     */
    public function haveAccessTokensBeenInvalidatedSince(DateTimeImmutable $since): bool
    {
        if ($this->lastTokenInvalidation === null) {
            return false;
        }

        return ($this->lastTokenInvalidation > $since);
    }

    /**
     * @return array
     */
    public function getPublicProperties(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'role' => $this->role,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'locale' => $this->locale
        ];
    }

    /**
     * @throws PermissionException if user is not admin
     */
    public function assertIsAdmin(): void
    {
        Assert::true(
            $this->hasRole(User::ROLE_ADMIN),
            PermissionException::class,
            'userRequiresAdminRole'
        );
    }

    /**
     * @param MatchEntity $match
     * @throws PermissionException if user cannot change the match
     */
    public function assertCanChangeMatch(MatchEntity $match): void
    {
        Assert::true(
            $this->hasRole(User::ROLE_ADMIN) || $this->isInTeam($match->getHomeTeam()) || $this->isInTeam($match->getGuestTeam()),
            PermissionException::class,
            'userNotPermittedToChangeMatch'
        );
    }

    /**
     * @param Team $team
     * @throws PermissionException if user cannot manage the team
     */
    public function assertCanManageTeam(Team $team): void
    {
        Assert::true(
            $this->hasRole(User::ROLE_ADMIN) || $this->isInTeam($team),
            PermissionException::class,
            'userNotPermittedToManageTeam'
        );
    }

    /**
     * Returns an array of valid roles
     *
     * @return string[]
     */
    public static function getRoles(): array
    {
        return [
            self::ROLE_ADMIN,
            self::ROLE_TEAM_MANAGER
        ];
    }

    /**
     * Returns an array of valid locales
     */
    public static function getLocales(): array
    {
        return ['de', 'en'];
    }

    /**
     * @return string|null
     */
    public function getLocale(): ?string
    {
        return $this->locale;
    }

    /**
     * @param string|null $locale
     */
    public function setLocale(?string $locale): void
    {
        if ($locale !== null) {
            Assert::true(
                in_array($locale, self::getLocales()),
                InvalidInputException::class,
                'userLocaleUnsupported'
            );
        }
        $this->locale = $locale;
    }
}
