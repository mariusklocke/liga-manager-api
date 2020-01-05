<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command;

use HexagonalPlayground\Application\TypeAssert;

class ChangeUserPasswordCommand implements CommandInterface
{
    /** @var string */
    private $newPassword;

    /**
     * @param string $newPassword
     */
    public function __construct($newPassword)
    {
        TypeAssert::assertString($newPassword, 'newPassword');
        $this->newPassword = $newPassword;
    }

    /**
     * @return string
     */
    public function getNewPassword(): string
    {
        return $this->newPassword;
    }
}