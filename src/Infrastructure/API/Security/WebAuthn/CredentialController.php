<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Security\WebAuthn;

use Exception;
use HexagonalPlayground\Application\TypeAssert;
use HexagonalPlayground\Domain\Exception\InvalidInputException;
use HexagonalPlayground\Domain\Exception\NotFoundException;
use HexagonalPlayground\Infrastructure\API\Controller as BaseController;
use HexagonalPlayground\Infrastructure\API\RequestParser;
use HexagonalPlayground\Infrastructure\API\Security\AuthReader;
use ParagonIE\ConstantTime\Base64UrlSafe;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use Webauthn\AuthenticatorAttestationResponse;
use Webauthn\AuthenticatorAttestationResponseValidator;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialLoader;

class CredentialController extends BaseController
{
    private PublicKeyCredentialSourceRepository $credentialRepository;
    private AuthReader $authReader;
    private PublicKeyCredentialLoader $credentialLoader;
    private AuthenticatorAttestationResponseValidator $authenticatorAttestationResponseValidator;
    private OptionsStoreInterface $creationOptionsStore;
    private RequestParser $requestParser;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        PublicKeyCredentialSourceRepository $credentialRepository,
        AuthReader $authReader,
        PublicKeyCredentialLoader $credentialLoader,
        AuthenticatorAttestationResponseValidator $authenticatorAttestationResponseValidator,
        OptionsStoreInterface $creationOptionsStore,
        RequestParser $requestParser
    ) {
        parent::__construct($responseFactory);
        $this->credentialRepository = $credentialRepository;
        $this->authReader = $authReader;
        $this->credentialLoader = $credentialLoader;
        $this->authenticatorAttestationResponseValidator = $authenticatorAttestationResponseValidator;
        $this->creationOptionsStore = $creationOptionsStore;
        $this->requestParser = $requestParser;
    }

    public function delete(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $id = Base64UrlSafe::decodeNoPadding($request->getAttribute('id'));
        } catch (Throwable $e) {
            throw new InvalidInputException('Failed to decode credential id. Please use base64url encoding.');
        }

        $user = UserConverter::convert($this->authReader->requireAuthContext($request)->getUser());

        /** @var PublicKeyCredential $credential */
        $credential = $this->credentialRepository->findOneByCredentialId($id);
        if (null === $credential || $credential->getUserHandle() !== $user->getId()) {
            throw new NotFoundException();
        }

        $this->credentialRepository->delete($credential);

        return $this->buildResponse();
    }

    public function get(ServerRequestInterface $request): ResponseInterface
    {
        $user = UserConverter::convert($this->authReader->requireAuthContext($request)->getUser());
        $credentials = $this->credentialRepository->findAllForUserEntity($user);

        return $this->buildJsonResponse($credentials);
    }

    public function post(ServerRequestInterface $request): ResponseInterface
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

        return $this->buildResponse();
    }
}
