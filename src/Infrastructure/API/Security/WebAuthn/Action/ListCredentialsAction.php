<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Security\WebAuthn\Action;

use HexagonalPlayground\Infrastructure\API\ActionInterface;
use HexagonalPlayground\Infrastructure\API\JsonEncodingTrait;
use HexagonalPlayground\Infrastructure\API\Security\AuthAware;
use HexagonalPlayground\Infrastructure\API\Security\WebAuthn\PublicKeyCredentialSourceRepository;
use HexagonalPlayground\Infrastructure\API\Security\WebAuthn\UserConverter;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ListCredentialsAction implements ActionInterface
{
    use AuthAware, JsonEncodingTrait;

    /** @var PublicKeyCredentialSourceRepository */
    private $credentialRepository;

    /**
     * @param PublicKeyCredentialSourceRepository $credentialRepository
     */
    public function __construct(PublicKeyCredentialSourceRepository $credentialRepository)
    {
        $this->credentialRepository = $credentialRepository;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $user = UserConverter::convert($this->requireAuthContext($request)->getUser());

        $credentials = $this->credentialRepository->findAllForUserEntity($user);

        return $this->toJson($response->withStatus(200), $credentials);
    }
}