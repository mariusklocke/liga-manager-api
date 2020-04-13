<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Security\WebAuthn\Action;

use HexagonalPlayground\Infrastructure\API\ActionInterface;
use HexagonalPlayground\Infrastructure\API\JsonResponseWriter;
use HexagonalPlayground\Infrastructure\API\Security\AuthReader;
use HexagonalPlayground\Infrastructure\API\Security\WebAuthn\PublicKeyCredentialSourceRepository;
use HexagonalPlayground\Infrastructure\API\Security\WebAuthn\UserConverter;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ListCredentialsAction implements ActionInterface
{
    /** @var PublicKeyCredentialSourceRepository */
    private $credentialRepository;

    /** @var AuthReader */
    private $authReader;

    /** @var JsonResponseWriter */
    private $responseWriter;

    /**
     * @param PublicKeyCredentialSourceRepository $credentialRepository
     * @param AuthReader $authReader
     * @param JsonResponseWriter $responseWriter
     */
    public function __construct(PublicKeyCredentialSourceRepository $credentialRepository, AuthReader $authReader, JsonResponseWriter $responseWriter)
    {
        $this->credentialRepository = $credentialRepository;
        $this->authReader = $authReader;
        $this->responseWriter = $responseWriter;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $user = UserConverter::convert($this->authReader->requireAuthContext($request)->getUser());

        $credentials = $this->credentialRepository->findAllForUserEntity($user);

        return $this->responseWriter->write($response->withStatus(200), $credentials);
    }
}