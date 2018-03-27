<?php
declare(strict_types=1);

namespace HexagonalPlayground\Tests\Functional;

class AuthHelper
{
    public function getBasicAuthHeaders(): array
    {
        $secret = base64_encode('user1:user1');
        return ['Authorization' => 'Basic ' . $secret];
    }

    public function getTokenAuthHeaders(string $token): array
    {
        return ['Authorization' => 'Bearer ' . $token];
    }
}