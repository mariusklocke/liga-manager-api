<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Security\WebAuthn;

use HexagonalPlayground\Application\Security\UserRepositoryInterface;
use HexagonalPlayground\Application\TypeAssert;
use HexagonalPlayground\Domain\Exception\NotFoundException;
use HexagonalPlayground\Infrastructure\API\Controller as BaseController;
use HexagonalPlayground\Infrastructure\API\RequestParser;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Webauthn\PublicKeyCredentialDescriptor;
use Webauthn\PublicKeyCredentialSourceRepository;

class LoginOptionsController extends BaseController
{
    private PublicKeyCredentialSourceRepository $credentialRepository;
    private RequestOptionsFactory $requestOptionsFactory;
    private OptionsStoreInterface $optionsStore;
    private FakeCredentialDescriptorFactory $fakeCredentialDescriptorFactory;
    private UserRepositoryInterface $userRepository;
    private RequestParser $requestParser;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        PublicKeyCredentialSourceRepository $credentialRepository,
        RequestOptionsFactory $requestOptionsFactory,
        OptionsStoreInterface $optionsStore,
        FakeCredentialDescriptorFactory $fakeCredentialDescriptorFactory,
        UserRepositoryInterface $userRepository,
        RequestParser $requestParser
    ) {
        parent::__construct($responseFactory);
        $this->credentialRepository = $credentialRepository;
        $this->requestOptionsFactory = $requestOptionsFactory;
        $this->optionsStore = $optionsStore;
        $this->fakeCredentialDescriptorFactory = $fakeCredentialDescriptorFactory;
        $this->userRepository = $userRepository;
        $this->requestParser = $requestParser;
    }

    public function post(ServerRequestInterface $request): ResponseInterface
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

        return $this->buildJsonResponse($options);
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
