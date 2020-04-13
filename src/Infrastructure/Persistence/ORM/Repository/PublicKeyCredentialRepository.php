<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\Persistence\ORM\Repository;

use HexagonalPlayground\Infrastructure\API\Security\WebAuthn\PublicKeyCredential;
use HexagonalPlayground\Infrastructure\API\Security\WebAuthn\PublicKeyCredentialSourceRepository;
use Webauthn\PublicKeyCredentialSource;
use Webauthn\PublicKeyCredentialUserEntity;

class PublicKeyCredentialRepository extends EntityRepository implements PublicKeyCredentialSourceRepository
{
    /**
     * @param string $publicKeyCredentialId
     * @return PublicKeyCredential|null
     */
    public function findOneByCredentialId(string $publicKeyCredentialId): ?PublicKeyCredentialSource
    {
        /** @var PublicKeyCredential $result */
        $result = $this->get($publicKeyCredentialId);

        return $result;
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
        $this->manager->persist($publicKeyCredentialSource);
        $this->manager->flush();
    }

    /**
     * @param PublicKeyCredential $entity
     */
    public function delete($entity): void
    {
        parent::delete($entity);
        $this->manager->flush();
    }

    /**
     * @inheritDoc
     */
    protected static function getEntityClass(): string
    {
        return PublicKeyCredential::class;
    }
}
