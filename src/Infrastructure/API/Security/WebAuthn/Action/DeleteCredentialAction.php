<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Security\WebAuthn\Action;

use Base64Url\Base64Url;
use HexagonalPlayground\Application\Exception\InvalidInputException;
use HexagonalPlayground\Application\Exception\NotFoundException;
use HexagonalPlayground\Infrastructure\API\ActionInterface;
use HexagonalPlayground\Infrastructure\API\Security\AuthAware;
use HexagonalPlayground\Infrastructure\API\Security\WebAuthn\PublicKeyCredential;
use HexagonalPlayground\Infrastructure\API\Security\WebAuthn\PublicKeyCredentialSourceRepository;
use HexagonalPlayground\Infrastructure\API\Security\WebAuthn\UserConverter;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class DeleteCredentialAction implements ActionInterface
{
    use AuthAware;

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
        try {
            $id = Base64Url::decode($args['id']);
        } catch (InvalidArgumentException $e) {
            throw new InvalidInputException('Failed to decode credential id. Please use base64url encoding.');
        }

        $user = UserConverter::convert($this->requireAuthContext($request)->getUser());

        /** @var PublicKeyCredential $credential */
        $credential = $this->credentialRepository->findOneByCredentialId($id);
        if (null === $credential || $credential->getUserHandle() !== $user->getId()) {
            throw new NotFoundException();
        }

        $this->credentialRepository->delete($credential);

        return $response->withStatus(204);
    }
}