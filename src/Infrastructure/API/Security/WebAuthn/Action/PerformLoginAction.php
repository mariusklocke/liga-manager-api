<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Security\WebAuthn\Action;

use DateTimeImmutable;
use Exception;
use HexagonalPlayground\Application\Security\AuthenticationException;
use HexagonalPlayground\Application\Security\TokenServiceInterface;
use HexagonalPlayground\Domain\Exception\InvalidInputException;
use HexagonalPlayground\Domain\Exception\NotFoundException;
use HexagonalPlayground\Application\Security\UserRepositoryInterface;
use HexagonalPlayground\Application\TypeAssert;
use HexagonalPlayground\Infrastructure\API\ActionInterface;
use HexagonalPlayground\Infrastructure\API\JsonResponseWriter;
use HexagonalPlayground\Infrastructure\API\RequestParser;
use HexagonalPlayground\Infrastructure\API\Security\WebAuthn\OptionsStoreInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Webauthn\AuthenticatorAssertionResponse;
use Webauthn\AuthenticatorAssertionResponseValidator;
use Webauthn\PublicKeyCredentialLoader;
use Webauthn\PublicKeyCredentialRequestOptions;

class PerformLoginAction implements ActionInterface
{
    private OptionsStoreInterface $optionsStore;
    private PublicKeyCredentialLoader $credentialLoader;
    private AuthenticatorAssertionResponseValidator $authenticatorAssertionResponseValidator;
    private UserRepositoryInterface $userRepository;
    private TokenServiceInterface $tokenService;
    private JsonResponseWriter $responseWriter;
    private RequestParser $requestParser;

    /**
     * @param OptionsStoreInterface $optionsStore
     * @param PublicKeyCredentialLoader $credentialLoader
     * @param AuthenticatorAssertionResponseValidator $authenticatorAssertionResponseValidator
     * @param UserRepositoryInterface $userRepository
     * @param TokenServiceInterface $tokenService
     * @param JsonResponseWriter $responseWriter
     * @param RequestParser $requestParser
     */
    public function __construct(OptionsStoreInterface $optionsStore, PublicKeyCredentialLoader $credentialLoader, AuthenticatorAssertionResponseValidator $authenticatorAssertionResponseValidator, UserRepositoryInterface $userRepository, TokenServiceInterface $tokenService, JsonResponseWriter $responseWriter, RequestParser $requestParser)
    {
        $this->optionsStore = $optionsStore;
        $this->credentialLoader = $credentialLoader;
        $this->authenticatorAssertionResponseValidator = $authenticatorAssertionResponseValidator;
        $this->userRepository = $userRepository;
        $this->tokenService = $tokenService;
        $this->responseWriter = $responseWriter;
        $this->requestParser = $requestParser;
    }

    /**
     * @inheritDoc
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $parsedBody = $this->requestParser->parseJson($request);
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

        $token = $this->tokenService->create($user, new DateTimeImmutable('now + 1 year'));

        $response = $response->withStatus(200)
            ->withHeader('X-Token', $this->tokenService->encode($token));

        return $this->responseWriter->write($response, $user->getPublicProperties());
    }
}
