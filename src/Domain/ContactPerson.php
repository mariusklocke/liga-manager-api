<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain;

class ContactPerson
{
    use EmailValidation;

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
        $this->validateEmail($email);
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->phone = $phone;
        $this->email = $email;
    }
}