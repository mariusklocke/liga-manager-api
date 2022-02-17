<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Security\WebAuthn;

use Webauthn\PublicKeyCredentialDescriptor;

class FakeCredentialDescriptorFactory
{
    /** @var string */
    private string $salt;

    /**
     * @param string $salt
     */
    public function __construct(string $salt)
    {
        $this->salt = $salt;
    }

    /**
     * @param string $username
     * @return PublicKeyCredentialDescriptor
     */
    public function create(string $username): PublicKeyCredentialDescriptor
    {
        $hash = hash('sha512', $this->salt . $username);
        $credentialId = hex2bin($hash);

        return new PublicKeyCredentialDescriptor(
            PublicKeyCredentialDescriptor::CREDENTIAL_TYPE_PUBLIC_KEY,
            $credentialId
        );
    }
}