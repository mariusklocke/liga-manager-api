<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Security\WebAuthn\Action;

use HexagonalPlayground\Infrastructure\API\ActionInterface;
use HexagonalPlayground\Infrastructure\API\JsonEncodingTrait;
use HexagonalPlayground\Infrastructure\API\Security\AuthAware;
use HexagonalPlayground\Infrastructure\API\Security\WebAuthn\CreationOptionsFactory;
use HexagonalPlayground\Infrastructure\API\Security\WebAuthn\OptionsStoreInterface;
use HexagonalPlayground\Infrastructure\API\Security\WebAuthn\UserConverter;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Webauthn\PublicKeyCredentialRpEntity;

class GetRegisterOptionsAction implements ActionInterface
{
    use AuthAware, JsonEncodingTrait;

    /** @var OptionsStoreInterface */
    private $optionsStore;

    /** @var CreationOptionsFactory */
    private $optionsFactory;

    /**
     * @param OptionsStoreInterface $optionsStore
     * @param CreationOptionsFactory $optionsFactory
     */
    public function __construct(OptionsStoreInterface $optionsStore, CreationOptionsFactory $optionsFactory)
    {
        $this->optionsStore = $optionsStore;
        $this->optionsFactory = $optionsFactory;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $user = $this->requireAuthContext($request)->getUser();

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