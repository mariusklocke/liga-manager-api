<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Security;

use DateTimeImmutable;
use HexagonalPlayground\Application\Security\AccessLinkGeneratorInterface;
use HexagonalPlayground\Application\Security\TokenServiceInterface;
use HexagonalPlayground\Domain\User;
use Psr\Http\Message\UriFactoryInterface;

class AccessLinkGenerator implements AccessLinkGeneratorInterface
{
    private TokenServiceInterface $tokenService;
    private UriFactoryInterface $uriFactory;
    private string $appBaseUrl;

    public function __construct(TokenServiceInterface $tokenService, UriFactoryInterface $uriFactory, string $appBaseUrl)
    {
        $this->tokenService = $tokenService;
        $this->uriFactory   = $uriFactory;
        $this->appBaseUrl   = $appBaseUrl;
    }

    public function generateAccessLink(User $user, DateTimeImmutable $expiresAt, string $path): string
    {
        $token = $this->tokenService->create($user, $expiresAt);
        $query = http_build_query([
            'token' => $this->tokenService->encode($token)
        ]);
        $link = $this->uriFactory->createUri($this->appBaseUrl)->withPath($path)->withQuery($query);

        return (string)$link;
    }

}
