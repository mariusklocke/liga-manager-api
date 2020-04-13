<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Security\WebAuthn\Action;

use HexagonalPlayground\Infrastructure\API\ActionInterface;
use HexagonalPlayground\Infrastructure\API\JsonEncodingTrait;
use HexagonalPlayground\Infrastructure\API\Security\AuthReader;
use HexagonalPlayground\Infrastructure\API\Security\WebAuthn\CreationOptionsFactory;
use HexagonalPlayground\Infrastructure\API\Security\WebAuthn\OptionsStoreInterface;
use HexagonalPlayground\Infrastructure\API\Security\WebAuthn\UserConverter;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Webauthn\PublicKeyCredentialRpEntity;

class GetRegisterOptionsAction implements ActionInterface
{
    use JsonEncodingTrait;

    /** @var OptionsStoreInterface */
    private $optionsStore;

    /** @var CreationOptionsFactory */
    private $optionsFactory;

    /** @var AuthReader */
    private $authReader;

    /**
     * @param OptionsStoreInterface $optionsStore
     * @param CreationOptionsFactory $optionsFactory
     * @param AuthReader $authReader
     */
    public function __construct(OptionsStoreInterface $optionsStore, CreationOptionsFactory $optionsFactory, AuthReader $authReader)
    {
        $this->optionsStore = $optionsStore;
        $this->optionsFactory = $optionsFactory;
        $this->authReader = $authReader;
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

        return $this->toJson($response->withStatus(200), $options);
    }
}