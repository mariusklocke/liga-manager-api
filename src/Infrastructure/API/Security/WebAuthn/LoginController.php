<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\Security\WebAuthn;

use DateTimeImmutable;
use Exception;
use HexagonalPlayground\Application\Security\AuthenticationException;
use HexagonalPlayground\Application\Security\TokenServiceInterface;
use HexagonalPlayground\Application\Security\UserRepositoryInterface;
use HexagonalPlayground\Application\TypeAssert;
use HexagonalPlayground\Domain\Exception\InvalidInputException;
use HexagonalPlayground\Domain\Exception\NotFoundException;
use HexagonalPlayground\Infrastructure\API\Controller as BaseController;
use HexagonalPlayground\Infrastructure\API\RequestParser;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Webauthn\AuthenticatorAssertionResponse;
use Webauthn\AuthenticatorAssertionResponseValidator;
use Webauthn\PublicKeyCredentialLoader;
use Webauthn\PublicKeyCredentialRequestOptions;

class LoginController extends BaseController
{
    private OptionsStoreInterface $optionsStore;
    private PublicKeyCredentialLoader $credentialLoader;
    private AuthenticatorAssertionResponseValidator $authenticatorAssertionResponseValidator;
    private UserRepositoryInterface $userRepository;
    private TokenServiceInterface $tokenService;
    private RequestParser $requestParser;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        OptionsStoreInterface $optionsStore,
        PublicKeyCredentialLoader $credentialLoader,
        AuthenticatorAssertionResponseValidator $authenticatorAssertionResponseValidator,
        UserRepositoryInterface $userRepository,
        TokenServiceInterface $tokenService,
        RequestParser $requestParser
    ) {
        parent::__construct($responseFactory);
        $this->optionsStore = $optionsStore;
        $this->credentialLoader = $credentialLoader;
        $this->authenticatorAssertionResponseValidator = $authenticatorAssertionResponseValidator;
        $this->userRepository = $userRepository;
        $this->tokenService = $tokenService;
        $this->requestParser = $requestParser;
    }

    public function post(ServerRequestInterface $request): ResponseInterface
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
        $response = $this->buildJsonResponse($user->getPublicProperties());

        return $response->withHeader('X-Token', $this->tokenService->encode($token));
    }
}
