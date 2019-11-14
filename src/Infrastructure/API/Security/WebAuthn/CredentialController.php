<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Security\WebAuthn;

use Base64Url\Base64Url;
use Exception;
use HexagonalPlayground\Application\Exception\InvalidInputException;
use HexagonalPlayground\Application\Exception\NotFoundException;
use HexagonalPlayground\Application\TypeAssert;
use HexagonalPlayground\Infrastructure\API\ResponseFactoryTrait;
use HexagonalPlayground\Infrastructure\API\Security\UserAware;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Webauthn\AuthenticatorAttestationResponse;
use Webauthn\AuthenticatorAttestationResponseValidator;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialLoader;
use Webauthn\PublicKeyCredentialRpEntity;

class CredentialController
{
    use UserAware;
    use ResponseFactoryTrait;

    /** @var PublicKeyCredentialSourceRepository */
    private $credentialRepository;

    /** @var PublicKeyCredentialLoader */
    private $credentialLoader;

    /** @var AuthenticatorAttestationResponseValidator */
    private $authenticatorAttestationResponseValidator;

    /** @var OptionsStoreInterface */
    private $creationOptionsStore;

    /** @var CreationOptionsFactory */
    private $creationOptionsFactory;

    /**
     * @param PublicKeyCredentialSourceRepository $credentialRepository
     * @param PublicKeyCredentialLoader $credentialLoader
     * @param AuthenticatorAttestationResponseValidator $authenticatorAttestationResponseValidator
     * @param OptionsStoreInterface $creationOptionsStore
     * @param CreationOptionsFactory $creationOptionsFactory
     */
    public function __construct(PublicKeyCredentialSourceRepository $credentialRepository, PublicKeyCredentialLoader $credentialLoader, AuthenticatorAttestationResponseValidator $authenticatorAttestationResponseValidator, OptionsStoreInterface $creationOptionsStore, CreationOptionsFactory $creationOptionsFactory)
    {
        $this->credentialRepository = $credentialRepository;
        $this->credentialLoader = $credentialLoader;
        $this->authenticatorAttestationResponseValidator = $authenticatorAttestationResponseValidator;
        $this->creationOptionsStore = $creationOptionsStore;
        $this->creationOptionsFactory = $creationOptionsFactory;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function options(ServerRequestInterface $request): ResponseInterface
    {
        $user = $this->getUserFromRequest($request);

        $rpEntity = new PublicKeyCredentialRpEntity(
            $request->getUri()->getHost(),
            $request->getUri()->getHost()
        );

        $userEntity = UserConverter::convert($user);

        $options = $this->creationOptionsFactory->create($rpEntity, $userEntity);

        $this->creationOptionsStore->save($user->getId(), $options);

        return $this->createResponse(200, $options);
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function create(ServerRequestInterface $request): ResponseInterface
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

        $user = $this->getUserFromRequest($request);
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

        return $this->createResponse(204);
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function findAll(ServerRequestInterface $request): ResponseInterface
    {
        $user = UserConverter::convert($this->getUserFromRequest($request));

        $credentials = $this->credentialRepository->findAllForUserEntity($user);

        return $this->createResponse(200, $credentials);
    }

    /**
     * @param ServerRequestInterface $request
     * @param string $id
     * @return ResponseInterface
     */
    public function deleteOne(ServerRequestInterface $request, string $id): ResponseInterface
    {
        try {
            $id = Base64Url::decode($id);
        } catch (InvalidArgumentException $e) {
            throw new InvalidInputException('Malformed parameter "id" in request URI');
        }

        $user = UserConverter::convert($this->getUserFromRequest($request));

        /** @var PublicKeyCredential $credential */
        $credential = $this->credentialRepository->findOneByCredentialId($id);
        if (null === $credential || $credential->getUserHandle() !== $user->getId()) {
            throw new NotFoundException();
        }

        $this->credentialRepository->delete($credential);

        return $this->createResponse(204);
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function deleteAll(ServerRequestInterface $request): ResponseInterface
    {
        $user = UserConverter::convert($this->getUserFromRequest($request));

        $credentials = $this->credentialRepository->findAllForUserEntity($user);
        foreach ($credentials as $credential) {
            $this->credentialRepository->delete($credential);
        }

        return $this->createResponse(200, ['count' => count($credentials)]);
    }
}
