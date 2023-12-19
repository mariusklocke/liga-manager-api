<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Security\WebAuthn\Action;

use HexagonalPlayground\Domain\Exception\NotFoundException;
use HexagonalPlayground\Application\Security\UserRepositoryInterface;
use HexagonalPlayground\Application\TypeAssert;
use HexagonalPlayground\Infrastructure\API\ActionInterface;
use HexagonalPlayground\Infrastructure\API\ResponseSerializer;
use HexagonalPlayground\Infrastructure\API\RequestParser;
use HexagonalPlayground\Infrastructure\API\Security\WebAuthn\FakeCredentialDescriptorFactory;
use HexagonalPlayground\Infrastructure\API\Security\WebAuthn\OptionsStoreInterface;
use HexagonalPlayground\Infrastructure\API\Security\WebAuthn\RequestOptionsFactory;
use HexagonalPlayground\Infrastructure\API\Security\WebAuthn\UserConverter;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Webauthn\PublicKeyCredentialDescriptor;
use Webauthn\PublicKeyCredentialSourceRepository;

class GetLoginOptionsAction implements ActionInterface
{
    private PublicKeyCredentialSourceRepository $credentialRepository;
    private RequestOptionsFactory $requestOptionsFactory;
    private OptionsStoreInterface $optionsStore;
    private FakeCredentialDescriptorFactory $fakeCredentialDescriptorFactory;
    private UserRepositoryInterface $userRepository;
    private ResponseSerializer $responseSerializer;
    private RequestParser $requestParser;

    /**
     * @param PublicKeyCredentialSourceRepository $credentialRepository
     * @param RequestOptionsFactory $requestOptionsFactory
     * @param OptionsStoreInterface $optionsStore
     * @param FakeCredentialDescriptorFactory $fakeCredentialDescriptorFactory
     * @param UserRepositoryInterface $userRepository
     * @param ResponseSerializer $responseSerializer
     * @param RequestParser $requestParser
     */
    public function __construct(PublicKeyCredentialSourceRepository $credentialRepository, RequestOptionsFactory $requestOptionsFactory, OptionsStoreInterface $optionsStore, FakeCredentialDescriptorFactory $fakeCredentialDescriptorFactory, UserRepositoryInterface $userRepository, ResponseSerializer $responseSerializer, RequestParser $requestParser)
    {
        $this->credentialRepository = $credentialRepository;
        $this->requestOptionsFactory = $requestOptionsFactory;
        $this->optionsStore = $optionsStore;
        $this->fakeCredentialDescriptorFactory = $fakeCredentialDescriptorFactory;
        $this->userRepository = $userRepository;
        $this->responseSerializer = $responseSerializer;
        $this->requestParser = $requestParser;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $parsedBody = $this->requestParser->parseJson($request);
        $email = $parsedBody['email'] ?? null;

        /** @var string $email */
        TypeAssert::assertString($email, 'email');

        $options = $this->requestOptionsFactory->create(
            $request->getUri()->getHost(),
            $this->getCredentialDescriptors($email)
        );

        $this->optionsStore->save($email, $options);

        return $this->responseSerializer->serializeJson($response->withStatus(200), $options);
    }

    /**
     * @param string $email
     * @return PublicKeyCredentialDescriptor[]
     */
    private function getCredentialDescriptors(string $email): array
    {
        $credentialDescriptors = [];

        try {
            $user = $this->userRepository->findByEmail($email);
            $credentials = $this->credentialRepository->findAllForUserEntity(UserConverter::convert($user));
            foreach ($credentials as $credentialSource) {
                $credentialDescriptors[] = $credentialSource->getPublicKeyCredentialDescriptor();
            }
        } catch (NotFoundException $e) {
            $credentialDescriptors[] = $this->fakeCredentialDescriptorFactory->create($email);
        }

        return $credentialDescriptors;
    }
}
