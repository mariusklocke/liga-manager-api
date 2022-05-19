<?php declare(strict_types=1);

namespace HexagonalPlayground\Application\Command\v2;

class InvalidateAccessTokensCommand implements CommandInterface
{
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
