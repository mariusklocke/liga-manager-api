<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Command;

class DeleteUserCommand implements CommandInterface
{
    use AuthenticationAware;

    /** @var string */
    private $userId;

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