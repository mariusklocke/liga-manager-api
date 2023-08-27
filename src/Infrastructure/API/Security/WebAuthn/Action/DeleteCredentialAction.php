<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Security\WebAuthn\Action;

use Base64Url\Base64Url;
use HexagonalPlayground\Domain\Exception\InvalidInputException;
use HexagonalPlayground\Domain\Exception\NotFoundException;
use HexagonalPlayground\Infrastructure\API\ActionInterface;
use HexagonalPlayground\Infrastructure\API\Security\AuthReader;
use HexagonalPlayground\Infrastructure\API\Security\WebAuthn\PublicKeyCredential;
use HexagonalPlayground\Infrastructure\API\Security\WebAuthn\PublicKeyCredentialSourceRepository;
use HexagonalPlayground\Infrastructure\API\Security\WebAuthn\UserConverter;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class DeleteCredentialAction implements ActionInterface
{
    /** @var PublicKeyCredentialSourceRepository */
    private PublicKeyCredentialSourceRepository $credentialRepository;

    /** @var AuthReader */
    private AuthReader $authReader;

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
        try {
            $id = Base64Url::decode($args['id']);
        } catch (InvalidArgumentException $e) {
            throw new InvalidInputException('Failed to decode credential id. Please use base64url encoding.');
        }

        $user = UserConverter::convert($this->authReader->requireAuthContext($request)->getUser());

        /** @var PublicKeyCredential $credential */
        $credential = $this->credentialRepository->findOneByCredentialId($id);
        if (null === $credential || $credential->getUserHandle() !== $user->getId()) {
            throw new NotFoundException();
        }

        $this->credentialRepository->delete($credential);

        return $response->withStatus(204);
    }
}
