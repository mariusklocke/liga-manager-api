<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command;

class ChangeUserPasswordCommand implements CommandInterface
{
    /** @var string */
    private string $newPassword;

    /**
     * @param string $newPassword
     */
    public function __construct(string $newPassword)
    {
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
