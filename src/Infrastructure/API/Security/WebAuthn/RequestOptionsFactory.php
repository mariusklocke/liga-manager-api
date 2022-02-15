<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Security\WebAuthn;

use Webauthn\PublicKeyCredentialDescriptor;
use Webauthn\PublicKeyCredentialRequestOptions;

class RequestOptionsFactory
{
    /** @var ChallengeGenerator */
    private ChallengeGenerator $challengeGenerator;

    /** @var int */
    private int $timeout;

    /**
     * @param ChallengeGenerator $challengeGenerator
     * @param int $timeout
     */
    public function __construct(ChallengeGenerator $challengeGenerator, int $timeout = 30000)
    {
        $this->challengeGenerator = $challengeGenerator;
        $this->timeout = $timeout;
    }

    /**
     * @param string $hostName
     * @param PublicKeyCredentialDescriptor[] $descriptors
     * @return PublicKeyCredentialRequestOptions
     */
    public function create(string $hostName, array $descriptors): PublicKeyCredentialRequestOptions
    {
        return new PublicKeyCredentialRequestOptions(
            $this->challengeGenerator->generate(),
            $this->timeout,
            $hostName,
            $descriptors
        );
    }
}