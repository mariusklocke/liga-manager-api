<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Security\WebAuthn\Action;

use DateTimeImmutable;
use Exception;
use HexagonalPlayground\Application\Security\AuthenticationException;
use HexagonalPlayground\Domain\Exception\InvalidInputException;
use HexagonalPlayground\Domain\Exception\NotFoundException;
use HexagonalPlayground\Application\Security\TokenFactoryInterface;
use HexagonalPlayground\Application\Security\UserRepositoryInterface;
use HexagonalPlayground\Application\TypeAssert;
use HexagonalPlayground\Infrastructure\API\ActionInterface;
use HexagonalPlayground\Infrastructure\API\JsonResponseWriter;
use HexagonalPlayground\Infrastructure\API\Security\WebAuthn\OptionsStoreInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Webauthn\AuthenticatorAssertionResponse;
use Webauthn\AuthenticatorAssertionResponseValidator;
use Webauthn\PublicKeyCredentialLoader;
use Webauthn\PublicKeyCredentialRequestOptions;

class PerformLoginAction implements ActionInterface
{
    /** @var OptionsStoreInterface */
    private OptionsStoreInterface $optionsStore;

    /** @var PublicKeyCredentialLoader */
    private PublicKeyCredentialLoader $credentialLoader;

    /** @var AuthenticatorAssertionResponseValidator */
    private AuthenticatorAssertionResponseValidator $authenticatorAssertionResponseValidator;

    /** @var UserRepositoryInterface */
    private UserRepositoryInterface $userRepository;

    /** @var TokenFactoryInterface */
    private TokenFactoryInterface $tokenFactory;

    /** @var JsonResponseWriter */
    private JsonResponseWriter $responseWriter;

    /**
     * @param OptionsStoreInterface $optionsStore
     * @param PublicKeyCredentialLoader $credentialLoader
     * @param AuthenticatorAssertionResponseValidator $authenticatorAssertionResponseValidator
     * @param UserRepositoryInterface $userRepository
     * @param TokenFactoryInterface $tokenFactory
     * @param JsonResponseWriter $responseWriter
     */
    public function __construct(OptionsStoreInterface $optionsStore, PublicKeyCredentialLoader $credentialLoader, AuthenticatorAssertionResponseValidator $authenticatorAssertionResponseValidator, UserRepositoryInterface $userRepository, TokenFactoryInterface $tokenFactory, JsonResponseWriter $responseWriter)
    {
        $this->optionsStore = $optionsStore;
        $this->credentialLoader = $credentialLoader;
        $this->authenticatorAssertionResponseValidator = $authenticatorAssertionResponseValidator;
        $this->userRepository = $userRepository;
        $this->tokenFactory = $tokenFactory;
        $this->responseWriter = $responseWriter;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $parsedBody = $request->getParsedBody();
        $email = $parsedBody['email'] ?? null;

        /** @var string $email */
        TypeAssert::assertString($email, 'email');

        try {
            $credential = $this->credentialLoader->loadArray($parsedBody);
            $authenticatorResponse = $credential->getResponse();
        } catch (Exception $e) {
            throw new InvalidInputException($e->getMessage());
        }

        if (!$authenticatorResponse instanceof AuthenticatorAssertionResponse) {
            throw new InvalidInputException('Response from authenticator is not an AuthenticatorAssertionResponse');
        }

        $options = $this->optionsStore->get($email);
        if (!$options instanceof PublicKeyCredentialRequestOptions) {
            throw new InvalidInputException('Cannot find request options for requested user');
        }

        try {
            $user = $this->userRepository->findByEmail($email);
        } catch (NotFoundException $e) {
            throw new AuthenticationException('Authentication failed');
        }

        try {
            $this->authenticatorAssertionResponseValidator->check(
                $credential->getRawId(),
                $authenticatorResponse,
                $options,
                $request,
                $user->getId()
            );
        } catch (Exception $e) {
            throw new AuthenticationException('Authentication failed');
        }

        $token = $this->tokenFactory->create($user, new DateTimeImmutable('now + 1 year'));

        $response = $response->withStatus(200)
            ->withHeader('X-Token', $token->encode());

        return $this->responseWriter->write($response, $user->getPublicProperties());
    }
}
