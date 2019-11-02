<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Security\WebAuthn;

class ChallengeGenerator
{
    /**
     * @return string
     */
    public function generate(): string
    {
        return random_bytes(32);
    }
}