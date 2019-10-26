<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\ORM;

use HexagonalPlayground\Application\Exception\NotFoundException;
use HexagonalPlayground\Infrastructure\API\Security\WebAuthn\PublicKeyCredential;
use HexagonalPlayground\Infrastructure\API\Security\WebAuthn\PublicKeyCredentialSourceRepository;
use Webauthn\PublicKeyCredentialSource;
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
        $this->_em->persist($publicKeyCredentialSource);
        $this->_em->flush();
    }

    public function delete($entity): void
    {
        parent::delete($entity);
        $this->_em->flush();
    }

}
