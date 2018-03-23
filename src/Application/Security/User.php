<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Security;

use DateTimeImmutable;

class User
{
    /** @var string */
    private $id;

    /** @var string */
    private $email;

    /** @var string */
    private $password;

    /** @var DateTimeImmutable|null */
    private $lastPasswordChange;

    /**
     * @param string $id
     * @param string $email
     * @param string $password
     */
    public function __construct(string $id, string $email, string $password)
    {
        $this->id = $id;
        $this->email = $email;
        $this->password = $this->hashPassword($password);
        $this->lastPasswordChange = null;
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
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
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
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
        ];
    }
}