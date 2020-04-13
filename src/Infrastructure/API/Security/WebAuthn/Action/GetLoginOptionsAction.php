<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Security\WebAuthn\Action;

use HexagonalPlayground\Application\Exception\NotFoundException;
use HexagonalPlayground\Application\Security\UserRepositoryInterface;
use HexagonalPlayground\Application\TypeAssert;
use HexagonalPlayground\Infrastructure\API\ActionInterface;
use HexagonalPlayground\Infrastructure\API\JsonResponseWriter;
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
    /** @var PublicKeyCredentialSourceRepository */
    private $credentialRepository;

    /** @var RequestOptionsFactory */
    private $requestOptionsFactory;

    /** @var OptionsStoreInterface */
    private $optionsStore;

    /** @var FakeCredentialDescriptorFactory */
    private $fakeCredentialDescriptorFactory;

    /** @var UserRepositoryInterface */
    private $userRepository;

    /** @var JsonResponseWriter */
    private $responseWriter;

    /**
     * @param PublicKeyCredentialSourceRepository $credentialRepository
     * @param RequestOptionsFactory $requestOptionsFactory
     * @param OptionsStoreInterface $optionsStore
     * @param FakeCredentialDescriptorFactory $fakeCredentialDescriptorFactory
     * @param UserRepositoryInterface $userRepository
     * @param JsonResponseWriter $responseWriter
     */
    public function __construct(PublicKeyCredentialSourceRepository $credentialRepository, RequestOptionsFactory $requestOptionsFactory, OptionsStoreInterface $optionsStore, FakeCredentialDescriptorFactory $fakeCredentialDescriptorFactory, UserRepositoryInterface $userRepository, JsonResponseWriter $responseWriter)
    {
        $this->credentialRepository = $credentialRepository;
        $this->requestOptionsFactory = $requestOptionsFactory;
        $this->optionsStore = $optionsStore;
        $this->fakeCredentialDescriptorFactory = $fakeCredentialDescriptorFactory;
        $this->userRepository = $userRepository;
        $this->responseWriter = $responseWriter;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $parsedBody = $request->getParsedBody();
        $email = $parsedBody['email'] ?? null;

        /** @var string $email */
        TypeAssert::assertString($email, 'email');

        $options = $this->requestOptionsFactory->create(
            $request->getUri()->getHost(),
            $this->getCredentialDescriptors($email)
        );

        $this->optionsStore->save($email, $options);

        return $this->responseWriter->write($response->withStatus(200), $options);
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