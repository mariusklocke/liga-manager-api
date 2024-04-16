<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Security;

use DateTimeImmutable;
use HexagonalPlayground\Domain\User;

interface AccessLinkGeneratorInterface
{
    /**
     * Generates a link (URL) for users containing a time-limited access token
     *
     * @param User $user
     * @param DateTimeImmutable $expiresAt
     * @param string $path
     * @return string
     */
    public function generateAccessLink(User $user, DateTimeImmutable $expiresAt, string $path): string;
}
