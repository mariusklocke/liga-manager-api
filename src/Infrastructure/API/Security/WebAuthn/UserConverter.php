<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Security\WebAuthn;

use HexagonalPlayground\Domain\User;
use Webauthn\PublicKeyCredentialUserEntity;

class UserConverter
{
    /**
     * @param User $user
     * @return PublicKeyCredentialUserEntity
     */
    public static function convert(User $user): PublicKeyCredentialUserEntity
    {
        return new PublicKeyCredentialUserEntity(
            $user->getEmail(),
            $user->getId(),
            $user->getEmail()
        );
    }
}