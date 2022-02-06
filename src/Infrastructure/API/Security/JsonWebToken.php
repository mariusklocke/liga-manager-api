<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Security;

use DateTimeImmutable;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use HexagonalPlayground\Application\Exception\AuthenticationException;
use HexagonalPlayground\Application\Security\TokenInterface;
use HexagonalPlayground\Infrastructure\Environment;

final class JsonWebToken implements TokenInterface
{
    private const DATE_FORMAT = 'U';
    private const ALGORITHM = 'HS256';

    /** @var string */
    private $userId;

    /** @var DateTimeImmutable */
    private $issuedAt;

    /** @var DateTimeImmutable */
    private $expiresAt;

    /** @var string */
    private static $secret;

    /**
     * @param string $userId
     * @param DateTimeImmutable $issuedAt
     * @param DateTimeImmutable $expiresAt
     */
    public function __construct(string $userId, DateTimeImmutable $issuedAt, DateTimeImmutable $expiresAt)
    {
        $this->userId = $userId;
        $this->issuedAt = $issuedAt;
        $this->expiresAt = $expiresAt;
    }

    /**
     * {@inheritdoc}
     */
    public function getIssuedAt(): DateTimeImmutable
    {
        return $this->issuedAt;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserId(): string
    {
        return $this->userId;
    }

    /**
     * @return string
     */
    public function encode(): string
    {
        $payload = [
            'sub' => $this->userId,
            'iat' => $this->issuedAt->format(self::DATE_FORMAT),
            'exp' => $this->expiresAt->format(self::DATE_FORMAT)
        ];
        return JWT::encode($payload, self::getSecret(), self::ALGORITHM);
    }

    /**
     * @param string $encoded
     * @return JsonWebToken
     */
    public static function decode(string $encoded): self
    {
        try {
            $key = new Key(self::getSecret(), self::ALGORITHM);
            $payload = JWT::decode($encoded, $key);
        } catch (\Exception $e) {
            throw new AuthenticationException('Invalid Token');
        }

        $subject   = $payload->sub;
        $issuedAt  = DateTimeImmutable::createFromFormat(self::DATE_FORMAT, $payload->iat);
        $expiresAt = isset($payload->exp)
            ? DateTimeImmutable::createFromFormat(self::DATE_FORMAT, $payload->exp)
            : new DateTimeImmutable();

        return new self($subject, $issuedAt, $expiresAt);
    }

    /**
     * @return string
     */
    private static function getSecret(): string
    {
        if (null === self::$secret) {
            self::$secret = hex2bin(Environment::get('JWT_SECRET'));
        }
        return self::$secret;
    }

    /**
     * Returns when the token expires
     *
     * @return DateTimeImmutable
     */
    public function getExpiresAt(): DateTimeImmutable
    {
        return $this->expiresAt;
    }

    /**
     * @param int $bytes
     * @return string
     */
    public static function generateSecret(int $bytes = 32): string
    {
        return bin2hex(random_bytes($bytes));
    }
}
