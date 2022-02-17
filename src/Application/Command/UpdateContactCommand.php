<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command;

abstract class UpdateContactCommand implements CommandInterface
{
    /** @var string */
    protected string $firstName;

    /** @var string */
    protected string $lastName;

    /** @var string */
    protected string $phone;

    /** @var string */
    protected string $email;

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
    public function getPhone(): string
    {
        return $this->phone;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }
}
