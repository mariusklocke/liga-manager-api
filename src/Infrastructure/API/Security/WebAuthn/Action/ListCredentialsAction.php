<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Security\WebAuthn\Action;

use HexagonalPlayground\Infrastructure\API\ActionInterface;
use HexagonalPlayground\Infrastructure\API\JsonEncodingTrait;
use HexagonalPlayground\Infrastructure\API\Security\AuthReader;
use HexagonalPlayground\Infrastructure\API\Security\WebAuthn\PublicKeyCredentialSourceRepository;
use HexagonalPlayground\Infrastructure\API\Security\WebAuthn\UserConverter;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ListCredentialsAction implements ActionInterface
{
    use JsonEncodingTrait;

    /** @var PublicKeyCredentialSourceRepository */
    private $credentialRepository;

    /** @var AuthReader */
    private $authReader;

    /**
     * @param PublicKeyCredentialSourceRepository $credentialRepository
     * @param AuthReader $authReader
     */
    public function __construct(PublicKeyCredentialSourceRepository $credentialRepository, AuthReader $authReader)
    {
        $this->credentialRepository = $credentialRepository;
        $this->authReader = $authReader;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $user = UserConverter::convert($this->authReader->requireAuthContext($request)->getUser());

        $credentials = $this->credentialRepository->findAllForUserEntity($user);

        return $this->toJson($response->withStatus(200), $credentials);
    }
}