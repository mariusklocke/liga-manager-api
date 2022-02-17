<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command;

class UpdatePitchContactCommand extends UpdateContactCommand
{
    /** @var string */
    private string $pitchId;

    /**
     * @param string $pitchId
     * @param string $firstName
     * @param string $lastName
     * @param string $phone
     * @param string $email
     */
    public function __construct(string $pitchId, string $firstName, string $lastName, string $phone, string $email)
    {
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
