<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command;

class SendPasswordResetMailCommand implements CommandInterface
{
    /** @var string */
    private string $email;

    /** @var string */
    private string $targetPath;

    /**
     * @param string $email
     * @param string $targetPath
     */
    public function __construct(string $email, string $targetPath)
    {
        $this->email      = $email;
        $this->targetPath = $targetPath;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getTargetPath(): string
    {
        return $this->targetPath;
    }
}
