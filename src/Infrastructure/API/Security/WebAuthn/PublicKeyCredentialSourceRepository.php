<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Security\WebAuthn;

use Webauthn\PublicKeyCredentialSourceRepository as BaseInterface;

interface PublicKeyCredentialSourceRepository extends BaseInterface
{
    /**
     * @param PublicKeyCredential $entity
     */
    public function delete($entity): void;
}