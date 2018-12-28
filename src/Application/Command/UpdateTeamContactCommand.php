<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command;

use HexagonalPlayground\Application\TypeAssert;

class UpdateTeamContactCommand extends UpdateContactCommand
{
    /** @var string */
    private $teamId;

    /**
     * @param string $teamId
     * @param string $firstName
     * @param string $lastName
     * @param string $phone
     * @param string $email
     */
    public function __construct($teamId, $firstName, $lastName, $phone, $email)
    {
        TypeAssert::assertString($teamId, 'teamId');
        TypeAssert::assertString($firstName, 'firstName');
        TypeAssert::assertString($lastName, 'lastName');
        TypeAssert::assertString($phone, 'phone');
        TypeAssert::assertString($email, 'email');
        $this->teamId = $teamId;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->phone = $phone;
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getTeamId(): string
    {
        return $this->teamId;
    }
}