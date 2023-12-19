<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Security\WebAuthn\Action;

use HexagonalPlayground\Infrastructure\API\ActionInterface;
use HexagonalPlayground\Infrastructure\API\ResponseSerializer;
use HexagonalPlayground\Infrastructure\API\Security\AuthReader;
use HexagonalPlayground\Infrastructure\API\Security\WebAuthn\PublicKeyCredentialSourceRepository;
use HexagonalPlayground\Infrastructure\API\Security\WebAuthn\UserConverter;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ListCredentialsAction implements ActionInterface
{
    private PublicKeyCredentialSourceRepository $credentialRepository;
    private AuthReader $authReader;
    private ResponseSerializer $responseSerializer;

    /**
     * @param PublicKeyCredentialSourceRepository $credentialRepository
     * @param AuthReader $authReader
     * @param ResponseSerializer $responseSerializer
     */
    public function __construct(PublicKeyCredentialSourceRepository $credentialRepository, AuthReader $authReader, ResponseSerializer $responseSerializer)
    {
        $this->credentialRepository = $credentialRepository;
        $this->authReader = $authReader;
        $this->responseSerializer = $responseSerializer;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $user = UserConverter::convert($this->authReader->requireAuthContext($request)->getUser());

        $credentials = $this->credentialRepository->findAllForUserEntity($user);

        return $this->responseSerializer->serializeJson($response->withStatus(200), $credentials);
    }
}
