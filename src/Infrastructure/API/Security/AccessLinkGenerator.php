<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Security;

use DateTimeImmutable;
use HexagonalPlayground\Application\Security\AccessLinkGeneratorInterface;
use HexagonalPlayground\Application\Security\TokenServiceInterface;
use HexagonalPlayground\Domain\User;
use HexagonalPlayground\Infrastructure\Config;
use Psr\Http\Message\UriFactoryInterface;

class AccessLinkGenerator implements AccessLinkGeneratorInterface
{
    private TokenServiceInterface $tokenService;
    private UriFactoryInterface $uriFactory;
    private Config $config;

    public function __construct(TokenServiceInterface $tokenService, UriFactoryInterface $uriFactory, Config $config)
    {
        $this->tokenService = $tokenService;
        $this->uriFactory   = $uriFactory;
        $this->config       = $config;
    }

    public function generateAccessLink(User $user, DateTimeImmutable $expiresAt, string $path): string
    {
        $baseUrl = $this->config->getValue('app.base.url', '');
        $token = $this->tokenService->create($user, $expiresAt);
        $query = http_build_query([
            'token' => $this->tokenService->encode($token)
        ]);
        $link = $this->uriFactory->createUri($baseUrl)->withPath($path)->withQuery($query);

        return (string)$link;
    }

}
