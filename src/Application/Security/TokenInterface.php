<?php
declare(strict_types=1);

namespace HexagonalPlayground\Application\Security;

use DateTimeImmutable;

interface TokenInterface
{
    /**
     * Returns when the token has been issued
     *
     * @return DateTimeImmutable
     */
    public function getIssuedAt(): DateTimeImmutable;

    /**
     * Returns the userId associated with the token
     *
     * @return string
     */
    public function getUserId(): string;
}