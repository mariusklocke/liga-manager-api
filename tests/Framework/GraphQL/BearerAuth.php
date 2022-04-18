<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\Framework\GraphQL;

class BearerAuth implements Auth
{
    private string $token;

    public function __construct(string $token)
    {
        $this->token = $token;
    }

    public function encode(): string
    {
        return 'Bearer ' . $this->token;
    }
}
