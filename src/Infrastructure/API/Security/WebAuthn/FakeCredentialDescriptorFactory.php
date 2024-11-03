<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Security\WebAuthn;

use HexagonalPlayground\Infrastructure\Config;
use Webauthn\PublicKeyCredentialDescriptor;

class FakeCredentialDescriptorFactory
{
    private Config $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @param string $username
     * @return PublicKeyCredentialDescriptor
     */
    public function create(string $username): PublicKeyCredentialDescriptor
    {
        $salt = $this->config->getValue('jwt.secret');
        $hash = hash('sha512', $salt . $username);
        $credentialId = hex2bin($hash);

        return new PublicKeyCredentialDescriptor(
            PublicKeyCredentialDescriptor::CREDENTIAL_TYPE_PUBLIC_KEY,
            $credentialId
        );
    }
}
