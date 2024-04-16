<?php declare(strict_types=1);

namespace HexagonalPlayground\Application\Command;

class SendInviteMailCommand implements CommandInterface
{
    /** @var string */
    private string $userId;

    /** @var string */
    private string $targetPath;

    /**
     * @param string $userId
     * @param string $targetPath
     */
    public function __construct(string $userId, string $targetPath)
    {
        $this->userId = $userId;
        $this->targetPath = $targetPath;
    }

    /**a
     * @return string
     */
    public function getUserId(): string
    {
        return $this->userId;
    }

    /**
     * @return string
     */
    public function getTargetPath(): string
    {
        return $this->targetPath;
    }
}
