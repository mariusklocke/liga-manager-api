<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Security\WebAuthn\Action;

use Exception;
use HexagonalPlayground\Application\Exception\InvalidInputException;
use HexagonalPlayground\Application\TypeAssert;
use HexagonalPlayground\Infrastructure\API\ActionInterface;
use HexagonalPlayground\Infrastructure\API\Security\AuthAware;
use HexagonalPlayground\Infrastructure\API\Security\WebAuthn\OptionsStoreInterface;
use HexagonalPlayground\Infrastructure\API\Security\WebAuthn\PublicKeyCredential;
use HexagonalPlayground\Infrastructure\API\Security\WebAuthn\PublicKeyCredentialSourceRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Webauthn\AuthenticatorAttestationResponse;
use Webauthn\AuthenticatorAttestationResponseValidator;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialLoader;

class RegisterCredentialAction implements ActionInterface
{
    use AuthAware;

    /** @var PublicKeyCredentialSourceRepository */
    private $credentialRepository;

    /** @var PublicKeyCredentialLoader */
    private $credentialLoader;

    /** @var AuthenticatorAttestationResponseValidator */
    private $authenticatorAttestationResponseValidator;

    /** @var OptionsStoreInterface */
    private $creationOptionsStore;

    /**
     * @param PublicKeyCredentialSourceRepository $credentialRepository
     * @param PublicKeyCredentialLoader $credentialLoader
     * @param AuthenticatorAttestationResponseValidator $authenticatorAttestationResponseValidator
     * @param OptionsStoreInterface $creationOptionsStore
     */
    public function __construct(PublicKeyCredentialSourceRepository $credentialRepository, PublicKeyCredentialLoader $credentialLoader, AuthenticatorAttestationResponseValidator $authenticatorAttestationResponseValidator, OptionsStoreInterface $creationOptionsStore)
    {
        $this->credentialRepository = $credentialRepository;
        $this->credentialLoader = $credentialLoader;
        $this->authenticatorAttestationResponseValidator = $authenticatorAttestationResponseValidator;
        $this->creationOptionsStore = $creationOptionsStore;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $parsedBody = $request->getParsedBody();
        $name = $parsedBody['name'] ?? null;

        /** @var string $name */
        TypeAssert::assertString($name, 'name');

        try {
            $credential = $this->credentialLoader->loadArray($parsedBody);
        } catch (Exception $e) {
            throw new InvalidInputException($e->getMessage());
        }

        $authenticatorResponse = $credential->getResponse();
        if (!$authenticatorResponse instanceof AuthenticatorAttestationResponse) {
            throw new InvalidInputException('Not an authenticator attestation response');
        }

        $user = $this->requireAuthContext($request)->getUser();
        $options = $this->creationOptionsStore->get($user->getId());
        if (!$options instanceof PublicKeyCredentialCreationOptions) {
            throw new InvalidInputException('Cannot find creation options for current user');
        }

        try {
            $credentialSource = $this->authenticatorAttestationResponseValidator->check($authenticatorResponse, $options, $request);
        } catch (Exception $e) {
            throw new InvalidInputException($e->getMessage());
        }

        $namedCredential = new PublicKeyCredential($credentialSource, $name);
        $this->credentialRepository->saveCredentialSource($namedCredential);

        return $response->withStatus(204);
    }
}