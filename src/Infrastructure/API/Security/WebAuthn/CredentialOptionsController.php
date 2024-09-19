<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Security\WebAuthn;

use HexagonalPlayground\Infrastructure\API\Controller as BaseController;
use HexagonalPlayground\Infrastructure\API\Security\AuthReader;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Webauthn\PublicKeyCredentialRpEntity;

class CredentialOptionsController extends BaseController
{
    private OptionsStoreInterface $optionsStore;
    private CreationOptionsFactory $optionsFactory;
    private AuthReader $authReader;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        OptionsStoreInterface $optionsStore,
        CreationOptionsFactory $optionsFactory,
        AuthReader $authReader
    ) {
        parent::__construct($responseFactory);
        $this->optionsStore = $optionsStore;
        $this->optionsFactory = $optionsFactory;
        $this->authReader = $authReader;
    }

    public function post(ServerRequestInterface $request): ResponseInterface
    {
        $user = $this->authReader->requireAuthContext($request)->getUser();
        $rpEntity = new PublicKeyCredentialRpEntity(
            $request->getUri()->getHost(),
            $request->getUri()->getHost()
        );
        $userEntity = UserConverter::convert($user);
        $options = $this->optionsFactory->create($rpEntity, $userEntity);
        $this->optionsStore->save($user->getId(), $options);

        return $this->buildJsonResponse($options);
    }
}
