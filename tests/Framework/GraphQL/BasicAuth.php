<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\Framework\GraphQL;

class BasicAuth implements Auth
{
    private string $email;
    private string $password;

    public function __construct(string $email, string $password)
    {
        $this->email = $email;
        $this->password = $password;
    }

    public function encode(): string
    {
        return 'Basic ' . base64_encode($this->email . ':' . $this->password);
    }
}
