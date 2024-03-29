<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API;

use HexagonalPlayground\Application\Security\AuthenticationException;
use HexagonalPlayground\Domain\Exception\ConflictException;
use HexagonalPlayground\Domain\Exception\InternalException;
use HexagonalPlayground\Domain\Exception\InvalidInputException;
use HexagonalPlayground\Domain\Exception\NotFoundException;
use HexagonalPlayground\Domain\Exception\PermissionException;
use HexagonalPlayground\Domain\Exception\UniquenessException;
use HexagonalPlayground\Infrastructure\API\Security\RateLimitException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Exception\HttpNotFoundException;
use Throwable;

class ErrorMiddleware implements MiddlewareInterface
{
    private LoggerInterface $logger;
    private ResponseFactoryInterface $responseFactory;
    private ResponseSerializer $responseSerializer;

    public function __construct(LoggerInterface $logger, ResponseFactoryInterface $responseFactory, ResponseSerializer $responseSerializer)
    {
        $this->logger = $logger;
        $this->responseFactory = $responseFactory;
        $this->responseSerializer = $responseSerializer;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (InvalidInputException|UniquenessException $exception) {
            $this->logException($exception, $request, true);
            return $this->createErrorResponse(400, $exception->getMessage(), $exception->getCode());
        } catch (AuthenticationException $exception) {
            $this->logException($exception, $request, true);
            return $this->createErrorResponse(401, $exception->getMessage(), $exception->getCode());
        } catch (PermissionException $exception) {
            $this->logException($exception, $request, true);
            return $this->createErrorResponse(403, $exception->getMessage(), $exception->getCode());
        } catch (NotFoundException $exception) {
            $this->logException($exception, $request, true);
            return $this->createErrorResponse(404, $exception->getMessage(), $exception->getCode());
        } catch (HttpNotFoundException $exception) {
            $this->logException($exception, $request, true);
            return $this->createErrorResponse(404, 'Route not found', 'ERR-NOT-FOUND');
        } catch (HttpMethodNotAllowedException $exception) {
            $this->logException($exception, $request, true);
            $headers = ['Allow' => implode(', ', $exception->getAllowedMethods())];
            return $this->createErrorResponse(405, $exception->getMessage(), 'ERR-METHOD-NOT-ALLOWED', $headers);
        } catch (ConflictException $exception) {
            $this->logException($exception, $request, true);
            return $this->createErrorResponse(409, $exception->getMessage(), $exception->getCode());
        } catch (RateLimitException $exception) {
            $this->logException($exception, $request, true);
            $headers = ['Retry-After' => 60];
            return $this->createErrorResponse(429, $exception->getMessage(), $exception->getCode(), $headers);
        } catch (MaintenanceModeException $exception) {
            $this->logException($exception, $request, false);
            $headers = ['Retry-After' => 60];
            return $this->createErrorResponse(503, $exception->getMessage(), $exception->getCode(), $headers);
        } catch (Throwable|InternalException $exception) {
            $this->logException($exception, $request, false);
            return $this->createErrorResponse(500, 'Internal Server Error', 'ERR-INTERNAL');
        }
    }

    /**
     * @param int $statusCode
     * @param string $message
     * @param string $errorCode
     * @param array $headers
     * @return ResponseInterface
     */
    private function createErrorResponse(int $statusCode, string $message, string $errorCode, array $headers = []): ResponseInterface
    {
        $response = $this->responseFactory->createResponse($statusCode);

        foreach ($headers as $name => $value) {
            $response = $response->withHeader($name, (string)$value);
        }

        return $this->responseSerializer->serializeJson($response, [
            'errors' => [
                [
                    'code' => $errorCode,
                    'message' => $message
                ]
            ]
        ]);
    }

    /**
     * @param Throwable $exception
     * @param ServerRequestInterface $request
     * @param bool $clientError
     * @return void
     */
    private function logException(Throwable $exception, ServerRequestInterface $request, bool $clientError): void
    {
        try {
            $message = $exception->getMessage();
            $context = [
                'exception' => [
                    'class' => get_class($exception),
                    'code' => $exception->getCode()
                ],
                'request' => [
                    'method' => $request->getMethod(),
                    'path' => $request->getUri()->getPath()
                ]
            ];

            if ($clientError) {
                $this->logger->notice($message, $context);
            } else {
                $this->logger->error($message, $context);
            }
        } catch (Throwable) {
            // Ignore errors when logging
        }
    }
}
