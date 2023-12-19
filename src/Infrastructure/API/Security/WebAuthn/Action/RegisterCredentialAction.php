<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Security\WebAuthn\Action;

use Exception;
use HexagonalPlayground\Domain\Exception\InvalidInputException;
use HexagonalPlayground\Application\TypeAssert;
use HexagonalPlayground\Infrastructure\API\ActionInterface;
use HexagonalPlayground\Infrastructure\API\RequestParser;
use HexagonalPlayground\Infrastructure\API\Security\AuthReader;
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
    private PublicKeyCredentialSourceRepository $credentialRepository;
    private PublicKeyCredentialLoader $credentialLoader;
    private AuthenticatorAttestationResponseValidator $authenticatorAttestationResponseValidator;
    private OptionsStoreInterface $creationOptionsStore;
    private AuthReader $authReader;
    private RequestParser $requestParser;

    /**
     * @param PublicKeyCredentialSourceRepository $credentialRepository
     * @param PublicKeyCredentialLoader $credentialLoader
     * @param AuthenticatorAttestationResponseValidator $authenticatorAttestationResponseValidator
     * @param OptionsStoreInterface $creationOptionsStore
     * @param AuthReader $authReader
     * @param RequestParser $requestParser
     */
    public function __construct(PublicKeyCredentialSourceRepository $credentialRepository, PublicKeyCredentialLoader $credentialLoader, AuthenticatorAttestationResponseValidator $authenticatorAttestationResponseValidator, OptionsStoreInterface $creationOptionsStore, AuthReader $authReader, RequestParser $requestParser)
    {
        $this->credentialRepository = $credentialRepository;
        $this->credentialLoader = $credentialLoader;
        $this->authenticatorAttestationResponseValidator = $authenticatorAttestationResponseValidator;
        $this->creationOptionsStore = $creationOptionsStore;
        $this->authReader = $authReader;
        $this->requestParser = $requestParser;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $parsedBody = $this->requestParser->parseJson($request);
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

        $user = $this->authReader->requireAuthContext($request)->getUser();
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
