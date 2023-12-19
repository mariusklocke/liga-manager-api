<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Security\WebAuthn\Action;

use HexagonalPlayground\Infrastructure\API\ActionInterface;
use HexagonalPlayground\Infrastructure\API\ResponseSerializer;
use HexagonalPlayground\Infrastructure\API\Security\AuthReader;
use HexagonalPlayground\Infrastructure\API\Security\WebAuthn\CreationOptionsFactory;
use HexagonalPlayground\Infrastructure\API\Security\WebAuthn\OptionsStoreInterface;
use HexagonalPlayground\Infrastructure\API\Security\WebAuthn\UserConverter;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Webauthn\PublicKeyCredentialRpEntity;

class GetRegisterOptionsAction implements ActionInterface
{
    private OptionsStoreInterface $optionsStore;
    private CreationOptionsFactory $optionsFactory;
    private AuthReader $authReader;
    private ResponseSerializer $responseSerializer;

    /**
     * @param OptionsStoreInterface $optionsStore
     * @param CreationOptionsFactory $optionsFactory
     * @param AuthReader $authReader
     * @param ResponseSerializer $responseSerializer
     */
    public function __construct(OptionsStoreInterface $optionsStore, CreationOptionsFactory $optionsFactory, AuthReader $authReader, ResponseSerializer $responseSerializer)
    {
        $this->optionsStore = $optionsStore;
        $this->optionsFactory = $optionsFactory;
        $this->authReader = $authReader;
        $this->responseSerializer = $responseSerializer;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $user = $this->authReader->requireAuthContext($request)->getUser();

        $rpEntity = new PublicKeyCredentialRpEntity(
            $request->getUri()->getHost(),
            $request->getUri()->getHost()
        );

        $userEntity = UserConverter::convert($user);

        $options = $this->optionsFactory->create($rpEntity, $userEntity);

        $this->optionsStore->save($user->getId(), $options);

        return $this->responseSerializer->serializeJson($response->withStatus(200), $options);
    }
}
