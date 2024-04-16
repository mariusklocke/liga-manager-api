<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Security;

use DateTimeImmutable;
use HexagonalPlayground\Application\Security\AccessLinkGeneratorInterface;
use HexagonalPlayground\Application\Security\TokenServiceInterface;
use HexagonalPlayground\Domain\User;
use Nyholm\Psr7\Uri;

class AccessLinkGenerator implements AccessLinkGeneratorInterface
{
    private TokenServiceInterface $tokenService;
    private string $appBaseUrl;

    public function __construct(TokenServiceInterface $tokenService, string $appBaseUrl)
    {
        $this->tokenService = $tokenService;
        $this->appBaseUrl   = $appBaseUrl;
    }

    public function generateAccessLink(User $user, DateTimeImmutable $expiresAt, string $path): string
    {
        $token = $this->tokenService->create($user, $expiresAt);
        $query = http_build_query([
            'token' => $this->tokenService->encode($token)
        ]);
        $link = (new Uri($this->appBaseUrl))->withPath($path)->withQuery($query);

        return $link->__toString();
    }

}
