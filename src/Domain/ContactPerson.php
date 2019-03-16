<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain;

use HexagonalPlayground\Domain\Util\Assert;

class ContactPerson
{
    /** @var string */
    private $firstName;

    /** @var string */
    private $lastName;

    /** @var string */
    private $phone;

    /** @var string */
    private $email;

    /**
     * @param string $firstName
     * @param string $lastName
     * @param string $phone
     * @param string $email
     */
    public function __construct(string $firstName, string $lastName, string $phone, string $email)
    {
        Assert::emailAddress($email, 'Invalid email address for contact');
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->phone = $phone;
        $this->email = $email;
    }

    /**
     * @param ContactPerson $other
     * @return bool
     */
    public function equals(ContactPerson $other): bool
    {
        return $this->firstName === $other->firstName
            && $this->lastName  === $other->lastName
            && $this->phone     === $other->phone
            && $this->email     === $other->email;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }
}