<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Security\WebAuthn\Action;

use HexagonalPlayground\Infrastructure\API\ActionInterface;
use HexagonalPlayground\Infrastructure\API\JsonResponseWriter;
use HexagonalPlayground\Infrastructure\API\Security\AuthReader;
use HexagonalPlayground\Infrastructure\API\Security\WebAuthn\CreationOptionsFactory;
use HexagonalPlayground\Infrastructure\API\Security\WebAuthn\OptionsStoreInterface;
use HexagonalPlayground\Infrastructure\API\Security\WebAuthn\UserConverter;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Webauthn\PublicKeyCredentialRpEntity;

class GetRegisterOptionsAction implements ActionInterface
{
    /** @var OptionsStoreInterface */
    private $optionsStore;

    /** @var CreationOptionsFactory */
    private $optionsFactory;

    /** @var AuthReader */
    private $authReader;

    /** @var JsonResponseWriter */
    private $responseWriter;

    /**
     * @param OptionsStoreInterface $optionsStore
     * @param CreationOptionsFactory $optionsFactory
     * @param AuthReader $authReader
     * @param JsonResponseWriter $responseWriter
     */
    public function __construct(OptionsStoreInterface $optionsStore, CreationOptionsFactory $optionsFactory, AuthReader $authReader, JsonResponseWriter $responseWriter)
    {
        $this->optionsStore = $optionsStore;
        $this->optionsFactory = $optionsFactory;
        $this->authReader = $authReader;
        $this->responseWriter = $responseWriter;
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

        return $this->responseWriter->write($response->withStatus(200), $options);
    }
}