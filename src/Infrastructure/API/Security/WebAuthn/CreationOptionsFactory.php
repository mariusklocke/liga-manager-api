<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Security\WebAuthn;

use Cose\Algorithm\Manager;
use Webauthn\AuthenticatorSelectionCriteria;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialDescriptor;
use Webauthn\PublicKeyCredentialParameters;
use Webauthn\PublicKeyCredentialRpEntity;
use Webauthn\PublicKeyCredentialUserEntity;

class CreationOptionsFactory
{
    /** @var ChallengeGenerator */
    private $challengeGenerator;

    /** @var int */
    private $timeout;

    /** @var Manager */
    private $algorithmManager;

    /**
     * @param ChallengeGenerator $challengeGenerator
     * @param int $timeout
     * @param Manager $algorithmManager
     */
    public function __construct(ChallengeGenerator $challengeGenerator, int $timeout, Manager $algorithmManager)
    {
        $this->challengeGenerator = $challengeGenerator;
        $this->timeout = $timeout;
        $this->algorithmManager = $algorithmManager;
    }

    /**
     * @param string $hostName
     * @param string $userId
     * @param string $userName
     * @return PublicKeyCredentialCreationOptions
     */
    public function create(string $hostName, string $userId, string $userName): PublicKeyCredentialCreationOptions
    {
        // RP Entity
        $rpEntity = new PublicKeyCredentialRpEntity(
            $hostName,
            $hostName,
            null
        );

        // User Entity
        $userEntity = new PublicKeyCredentialUserEntity(
            $userName,
            $userId,
            $userName,
            null
        );

        // Public Key Credential Parameters
        $publicKeyCredentialParametersList = [];
        foreach ($this->algorithmManager->all() as $algorithm) {
            $publicKeyCredentialParametersList[] = new PublicKeyCredentialParameters(
                PublicKeyCredentialDescriptor::CREDENTIAL_TYPE_PUBLIC_KEY,
                $algorithm::identifier()
            );
        }

        $authenticatorSelectionCriteria = new AuthenticatorSelectionCriteria();

        return new PublicKeyCredentialCreationOptions(
            $rpEntity,
            $userEntity,
            $this->challengeGenerator->generate(),
            $publicKeyCredentialParametersList,
            $this->timeout,
            [],
            $authenticatorSelectionCriteria,
            PublicKeyCredentialCreationOptions::ATTESTATION_CONVEYANCE_PREFERENCE_DIRECT,
            null
        );
    }
}