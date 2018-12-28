<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command;

use HexagonalPlayground\Application\TypeAssert;

class UpdatePitchContactCommand extends UpdateContactCommand
{
    /** @var string */
    private $pitchId;

    /**
     * @param string $pitchId
     * @param string $firstName
     * @param string $lastName
     * @param string $phone
     * @param string $email
     */
    public function __construct($pitchId, $firstName, $lastName, $phone, $email)
    {
        TypeAssert::assertString($pitchId, 'pitchId');
        TypeAssert::assertString($firstName, 'firstName');
        TypeAssert::assertString($lastName, 'lastName');
        TypeAssert::assertString($phone, 'phone');
        TypeAssert::assertString($email, 'email');
        $this->pitchId = $pitchId;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->phone = $phone;
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getPitchId(): string
    {
        return $this->pitchId;
    }
}