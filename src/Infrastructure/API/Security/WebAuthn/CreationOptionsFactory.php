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
    public function __construct(ChallengeGenerator $challengeGenerator, Manager $algorithmManager, int $timeout = 30000)
    {
        $this->challengeGenerator = $challengeGenerator;
        $this->algorithmManager = $algorithmManager;
        $this->timeout = $timeout;
    }

    /**
     * @param PublicKeyCredentialRpEntity $rpEntity
     * @param PublicKeyCredentialUserEntity $userEntity
     * @return PublicKeyCredentialCreationOptions
     */
    public function create(PublicKeyCredentialRpEntity $rpEntity, PublicKeyCredentialUserEntity $userEntity): PublicKeyCredentialCreationOptions
    {
        $authenticatorSelectionCriteria = new AuthenticatorSelectionCriteria();

        return new PublicKeyCredentialCreationOptions(
            $rpEntity,
            $userEntity,
            $this->challengeGenerator->generate(),
            $this->buildCredentialParametersList(),
            $this->timeout,
            [],
            $authenticatorSelectionCriteria,
            PublicKeyCredentialCreationOptions::ATTESTATION_CONVEYANCE_PREFERENCE_DIRECT,
            null
        );
    }

    /**
     * @return array|PublicKeyCredentialParameters[]
     */
    private function buildCredentialParametersList(): array
    {
        $list = [];
        foreach ($this->algorithmManager->all() as $algorithm) {
            $list[] = new PublicKeyCredentialParameters(
                PublicKeyCredentialDescriptor::CREDENTIAL_TYPE_PUBLIC_KEY,
                $algorithm::identifier()
            );
        }

        return $list;
    }
}