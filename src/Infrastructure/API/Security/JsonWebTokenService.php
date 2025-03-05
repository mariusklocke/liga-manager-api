<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Security;

use DateTimeImmutable;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use HexagonalPlayground\Application\Security\AuthenticationException;
use HexagonalPlayground\Application\Security\TokenInterface;
use HexagonalPlayground\Application\Security\TokenServiceInterface;
use HexagonalPlayground\Domain\User;
use HexagonalPlayground\Infrastructure\Config;
use HexagonalPlayground\Infrastructure\Filesystem\File;

class JsonWebTokenService implements TokenServiceInterface
{
    private const DATE_FORMAT = 'U';
    private const ALGORITHM = 'HS256';

    private Key $privateKey;

    public function __construct(Config $config)
    {
        if ($config->getValue('jwt.secret.file')) {
            $key = (new File($config->getValue('jwt.secret.file')))->read();
        } else {
            $key = $config->getValue('jwt.secret');
        }
        $this->privateKey = new Key(hex2bin($key), self::ALGORITHM);
    }

    /**
     * @param User $user
     * @param DateTimeImmutable $expiresAt
     * @return JsonWebToken
     */
    public function create(User $user, DateTimeImmutable $expiresAt): TokenInterface
    {
        return new JsonWebToken($user->getId(), new DateTimeImmutable(), $expiresAt);
    }

    /**
     * @param TokenInterface $token
     * @return string
     */
    public function encode(TokenInterface $token): string
    {
        $payload = [
            'sub' => $token->getUserId(),
            'iat' => $token->getIssuedAt()->format(self::DATE_FORMAT),
            'exp' => $token->getExpiresAt()->format(self::DATE_FORMAT)
        ];

        return JWT::encode($payload, $this->privateKey->getKeyMaterial(), $this->privateKey->getAlgorithm());
    }

    /**
     * @param string $encodedToken
     * @return TokenInterface
     */
    public function decode(string $encodedToken): TokenInterface
    {
        try {
            $payload = JWT::decode($encodedToken, $this->privateKey);
        } catch (\Exception $e) {
            throw new AuthenticationException('Invalid Token');
        }

        $subject   = $payload->sub;
        $issuedAt  = DateTimeImmutable::createFromFormat(self::DATE_FORMAT, $payload->iat);
        $expiresAt = new DateTimeImmutable();

        if (isset($payload->exp)) {
            $expiresAt = DateTimeImmutable::createFromFormat(self::DATE_FORMAT, $payload->exp);
        }

        return new JsonWebToken($subject, $issuedAt, $expiresAt);
    }
}
