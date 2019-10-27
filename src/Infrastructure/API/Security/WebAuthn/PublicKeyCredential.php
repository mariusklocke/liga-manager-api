<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Security\WebAuthn;

use DateTimeImmutable;
use Webauthn\PublicKeyCredentialSource;

class PublicKeyCredential extends PublicKeyCredentialSource
{
    /** @var string */
    private $name;

    /** @var DateTimeImmutable */
    private $createdAt;

    /** @var DateTimeImmutable|null */
    private $updatedAt;

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
        $this->createdAt = new DateTimeImmutable();
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
        $this->setUpdatedAt();
    }

    /**
     * @param int $counter
     */
    public function setCounter(int $counter): void
    {
        parent::setCounter($counter);
        $this->setUpdatedAt();
    }

    private function setUpdatedAt(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        $data = parent::jsonSerialize();
        $data['name'] = $this->name;
        $data['createdAt'] = $this->createdAt->format(DATE_ATOM);
        if ($this->updatedAt !== null) {
            $data['updatedAt'] = $this->updatedAt->format(DATE_ATOM);
        }

        return $data;
    }
}