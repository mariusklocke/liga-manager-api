<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command;

class DeleteUserCommand implements CommandInterface
{
    /** @var string */
    private string $userId;

    /**
     * @param string $userId
     */
    public function __construct(string $userId)
    {
        $this->userId = $userId;
    }

    /**
     * @return string
     */
    public function getUserId(): string
    {
        return $this->userId;
    }
}