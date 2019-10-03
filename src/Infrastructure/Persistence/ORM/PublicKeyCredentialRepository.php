<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\ORM;

use HexagonalPlayground\Application\Exception\NotFoundException;
use HexagonalPlayground\Infrastructure\API\Security\WebAuthn\PublicKeyCredential;
use Webauthn\PublicKeyCredentialSource;
use Webauthn\PublicKeyCredentialSourceRepository;
use Webauthn\PublicKeyCredentialUserEntity;

class PublicKeyCredentialRepository extends BaseRepository implements PublicKeyCredentialSourceRepository
{
    /**
     * @param string $publicKeyCredentialId
     * @return PublicKeyCredential|null
     */
    public function findOneByCredentialId(string $publicKeyCredentialId): ?PublicKeyCredentialSource
    {
        try {
            /** @var PublicKeyCredential $result */
            $result = $this->find($publicKeyCredentialId);
            return $result;
        } catch (NotFoundException $e) {
            return null;
        }
    }

    /**
     * @param PublicKeyCredentialUserEntity $publicKeyCredentialUserEntity
     * @return PublicKeyCredential[]
     */
    public function findAllForUserEntity(PublicKeyCredentialUserEntity $publicKeyCredentialUserEntity): array
    {
        return $this->findBy(['userHandle' => $publicKeyCredentialUserEntity->getId()]);
    }

    /**
     * @param PublicKeyCredentialSource $publicKeyCredentialSource
     */
    public function saveCredentialSource(PublicKeyCredentialSource $publicKeyCredentialSource): void
    {
        $this->save($publicKeyCredentialSource);
    }
}
