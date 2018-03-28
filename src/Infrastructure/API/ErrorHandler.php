<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API;

use HexagonalPlayground\Application\Exception\AuthenticationException;
use HexagonalPlayground\Application\Exception\AuthorizationException;
use HexagonalPlayground\Application\Exception\NotFoundException;
use HexagonalPlayground\Domain\DomainException;
use HexagonalPlayground\Infrastructure\API\Exception\BadRequestException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Slim\Exception\MethodNotAllowedException;
use Slim\Exception\NotFoundException as RouteNotFoundException;
use Slim\Http\Response;
use Throwable;

class ErrorHandler
{
    /** @var LoggerInterface */
    private $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param Throwable $throwable
     * @return Response
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response, Throwable $throwable) : Response
    {
        switch (true) {
            case ($throwable instanceof AuthenticationException):
                $this->logger->notice((string)$throwable);
                return $this->createUnauthorizedResponse($throwable->getMessage());
            case ($throwable instanceof AuthorizationException):
                $this->logger->notice((string)$throwable);
                return $this->createForbiddenResponse($throwable->getMessage());
            case ($throwable instanceof NotFoundException):
            case ($throwable instanceof RouteNotFoundException):
                $this->logger->notice((string)$throwable);
                return $this->createNotFoundResponse($throwable->getMessage());
            case ($throwable instanceof DomainException):
            case ($throwable instanceof BadRequestException):
                $this->logger->notice((string)$throwable);
                return $this->createBadRequestResponse($throwable->getMessage());
            case ($throwable instanceof MethodNotAllowedException):
                $this->logger->notice((string)$throwable);
                return $this->createMethodNotAllowedResponse($throwable->getAllowedMethods());
        }

        $this->logger->error((string)$throwable);
        return $this->createInternalErrorResponse();
    }

    /**
     * @return Response
     */
    private function createInternalErrorResponse() : Response
    {
        $response = new Response(500);
        return $response->withJson(['title' => 'Internal Server Error']);
    }

    /**
     * @param array $allowedMethods
     * @return Response
     */
    private function createMethodNotAllowedResponse(array $allowedMethods) : Response
    {
        $response = new Response(405);
        return $response
            ->withHeader('Allow', implode(', ', $allowedMethods))
            ->withJson(['title' => 'Method Not Allowed', 'allowed_methods' => $allowedMethods]);
    }

    /**
     * @param string $message
     * @return Response
     */
    private function createNotFoundResponse(string $message) : Response
    {
        $response = new Response(404);
        return $response->withJson(['title' => 'Not Found', 'message' => $message]);
    }

    /**
     * @param string $message
     * @return Response
     */
    private function createBadRequestResponse(string $message) : Response
    {
        return (new Response(400))->withJson(['title' => 'Bad Request', 'message' => $message]);
    }

    /**
     * @param string $message
     * @return Response
     */
    private function createUnauthorizedResponse(string $message): Response
    {
        return (new Response(401))->withJson(['title' => 'Unauthorized', 'message' => $message]);
    }

    /**
     * @param string $message
     * @return Response
     */
    private function createForbiddenResponse(string $message): Response
    {
        return (new Response(403))->withJson(['title' => 'Forbidden', 'message' => $message]);
    }
}
