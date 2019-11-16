<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain\Value;

use HexagonalPlayground\Domain\Util\Assert;

class ContactPerson extends ValueObject
{
    /** @var string */
    protected $firstName;

    /** @var string */
    protected $lastName;

    /** @var string */
    protected $phone;

    /** @var string */
    protected $email;

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
}
