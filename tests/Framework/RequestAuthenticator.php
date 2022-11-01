<?php declare(strict_types=1);

namespace HexagonalPlayground\Tests\Framework;

use Psr\Http\Message\RequestInterface;

class RequestAuthenticator
{
    public function withAdminAuth(RequestInterface $request): RequestInterface
    {
        return $request->withHeader('Authorization', $this->buildBasicAuth(
            getenv('ADMIN_EMAIL'),
            getenv('ADMIN_PASSWORD')
        ));
    }

    private function buildBasicAuth(string $email, string $password): string
    {
        return 'Basic ' . base64_encode($email . ':' . $password);
    }
}
