<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Security;

use DateTimeImmutable;
use HexagonalPlayground\Application\Security\TokenInterface;

final class JsonWebToken implements TokenInterface
{
    /** @var string */
    private string $userId;

    /** @var DateTimeImmutable */
    private DateTimeImmutable $issuedAt;

    /** @var DateTimeImmutable */
    private DateTimeImmutable $expiresAt;

    /** @var string|null */
    private static ?string $secret = null;

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
     * Returns when the token expires
     *
     * @return DateTimeImmutable
     */
    public function getExpiresAt(): DateTimeImmutable
    {
        return $this->expiresAt;
    }
}
