<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command\v2;

class UpdateUserPasswordCommand extends UpdateCommand implements CommandInterface
{
    /** @var string */
    private string $oldPassword;

    /** @var string */
    private string $newPassword;

    /**
     * @param string $id
     * @param string $oldPassword
     * @param string $newPassword
     */
    public function __construct(string $id, string $oldPassword, string $newPassword)
    {
        $this->id = $id;
        $this->oldPassword = $oldPassword;
        $this->newPassword = $newPassword;
    }

    /**
     * @return string
     */
    public function getOldPassword(): string
    {
        return $this->oldPassword;
    }

    /**
     * @return string
     */
    public function getNewPassword(): string
    {
        return $this->newPassword;
    }
}
