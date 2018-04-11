<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Security;

use DateTimeImmutable;
use Firebase\JWT\JWT;
use HexagonalPlayground\Application\Exception\AuthenticationException;
use HexagonalPlayground\Application\Security\TokenInterface;
use UnexpectedValueException;

final class JsonWebToken implements TokenInterface
{
    private const DATE_FORMAT = DateTimeImmutable::RFC3339;
    private const ALGORITHM = 'HS256';

    /** @var string */
    private $userId;

    /** @var DateTimeImmutable */
    private $issuedAt;

    /** @var string */
    private static $secret;

    /**
     * @param string $userId
     * @param DateTimeImmutable $issuedAt
     */
    public function __construct(string $userId, DateTimeImmutable $issuedAt)
    {
        $this->userId = $userId;
        $this->issuedAt = $issuedAt;
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
        $payload = ['sub' => $this->userId, 'iat' => $this->issuedAt->format(self::DATE_FORMAT)];
        return JWT::encode($payload, self::getSecret(), self::ALGORITHM);
    }

    /**
     * @param string $encoded
     * @return JsonWebToken
     */
    public static function decode(string $encoded): self
    {
        try {
            $payload = JWT::decode($encoded, self::getSecret(), [self::ALGORITHM]);
        } catch (UnexpectedValueException $e) {
            throw new AuthenticationException('Invalid Token');
        }
        return new self($payload->sub, DateTimeImmutable::createFromFormat(self::DATE_FORMAT, $payload->iat));
    }

    /**
     * @return string
     */
    private static function getSecret(): string
    {
        if (null === self::$secret) {
            self::$secret = file_get_contents(getenv('JWT_SECRET_PATH'));
        }
        return self::$secret;
    }
}