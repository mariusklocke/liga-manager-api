<?php
declare(strict_types=1);

namespace HexagonalPlayground\Domain\Value;

use HexagonalPlayground\Domain\Exception\InvalidInputException;
use HexagonalPlayground\Domain\Util\Assert;
use HexagonalPlayground\Domain\Util\StringUtils;

class ContactPerson extends ValueObject
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
     * @param string $firstName
     * @param string $lastName
     * @param string $phone
     * @param string $email
     */
    public function __construct(string $firstName, string $lastName, string $phone, string $email)
    {
        Assert::true(
            StringUtils::isValidEmailAddress($email),
            InvalidInputException::class,
            'contactEmailInvalid'
        );
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->phone = $phone;
        $this->email = $email;
    }
}
