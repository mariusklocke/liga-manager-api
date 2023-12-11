<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Security;

use DateTimeImmutable;
use HexagonalPlayground\Domain\User;

interface TokenServiceInterface
{
    /**
     * Create a new token for the given user
     *
     * @param User $user
     * @param DateTimeImmutable $expiresAt
     * @return TokenInterface
     */
    public function create(User $user, DateTimeImmutable $expiresAt): TokenInterface;

    /**
     * Encodes a token to string
     *
     * @param TokenInterface $token
     * @return string
     */
    public function encode(TokenInterface $token): string;

    /**
     * Decodes a token from string
     *
     * @param string $encodedToken
     * @return TokenInterface
     */
    public function decode(string $encodedToken): TokenInterface;
}
