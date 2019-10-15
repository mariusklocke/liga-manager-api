<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\Framework;

use HexagonalPlayground\Infrastructure\Environment;
use Psr\Http\Message\RequestInterface;

class RequestAuthenticator
{
    public function withAdminAuth(RequestInterface $request): RequestInterface
    {
        return $request->withHeader('Authorization', $this->buildBasicAuth(
            Environment::get('ADMIN_EMAIL'),
            Environment::get('ADMIN_PASSWORD')
        ));
    }

    private function buildBasicAuth(string $email, string $password): string
    {
        return 'Basic ' . base64_encode($email . ':' . $password);
    }
}