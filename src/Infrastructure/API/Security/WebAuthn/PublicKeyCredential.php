<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Security\WebAuthn;

use Webauthn\PublicKeyCredentialSource;

class PublicKeyCredential extends PublicKeyCredentialSource
{
    /** @var string */
    private $name;

    public function __construct(PublicKeyCredentialSource $parent, string $name)
    {
        parent::__construct(
            $parent->getPublicKeyCredentialId(),
            $parent->getType(),
            $parent->getTransports(),
            $parent->getAttestationType(),
            $parent->getTrustPath(),
            $parent->getAaguid(),
            $parent->getCredentialPublicKey(),
            $parent->getUserHandle(),
            $parent->getCounter()
        );
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        $data = parent::jsonSerialize();
        $data['name'] = $this->name;

        return $data;
    }
}