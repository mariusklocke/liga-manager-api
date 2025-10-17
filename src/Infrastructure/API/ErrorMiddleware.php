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
use Psr\Log\LogLevel;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Exception\HttpNotFoundException;
use Throwable;

class ErrorMiddleware implements MiddlewareInterface
{
    use ResponseBuilderTrait;

    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger, ResponseFactoryInterface $responseFactory)
    {
        $this->logger = $logger;
        $this->responseFactory = $responseFactory;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (InvalidInputException|UniquenessException $exception) {
            return $this->createErrorResponse(400, $exception, $request);
        } catch (AuthenticationException $exception) {
            return $this->createErrorResponse(401, $exception, $request);
        } catch (PermissionException $exception) {
            return $this->createErrorResponse(403, $exception, $request);
        } catch (NotFoundException $exception) {
            return $this->createErrorResponse(404, $exception, $request);
        } catch (HttpNotFoundException $exception) {
            return $this->createErrorResponse(404, $exception, $request);
        } catch (HttpMethodNotAllowedException $exception) {
            return $this->createErrorResponse(405, $exception, $request);
        } catch (ConflictException $exception) {
            return $this->createErrorResponse(409, $exception, $request);
        } catch (RateLimitException $exception) {
            return $this->createErrorResponse(429, $exception, $request);
        } catch (MaintenanceModeException $exception) {
            return $this->createErrorResponse(503, $exception, $request);
        } catch (Throwable|InternalException $exception) {
            return $this->createErrorResponse(500, $exception, $request);
        }
    }

    /**
     * @param int $statusCode
     * @param Throwable $exception
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    private function createErrorResponse(int $statusCode, Throwable $exception, ServerRequestInterface $request): ResponseInterface
    {
        $error = [];
        $error['message'] = $exception->getMessage();
        $error['code'] = $exception->getCode();
        $headers = [];

        if ($statusCode === 500) {
            $error['message'] = 'Internal Server Error';
            $error['code'] = 'ERR-INTERNAL';
        }

        if ($statusCode === 429 || $statusCode === 503) {
            $headers['Retry-After'] = '60';
        }

        if ($exception instanceof HttpNotFoundException) {
            $error['message'] = 'Route not found';
            $error['code'] = 'ERR-NOT-FOUND';
        }

        if ($exception instanceof HttpMethodNotAllowedException) {
            $error['code'] = 'ERR-METHOD-NOT-ALLOWED';
            $headers['Allow'] = implode(', ', $exception->getAllowedMethods());
        }

        $response = $this->buildJsonResponse(['errors' => [$error]], $statusCode);

        foreach ($headers as $name => $value) {
            $response = $response->withHeader($name, $value);
        }

        try {
            $logLevel   = $statusCode === 500 ? LogLevel::ERROR : LogLevel::NOTICE;
            $logMessage = $exception->getMessage();

            $this->logger->log($logLevel, $logMessage, [
                'exception' => $exception,
                'request' => $request,
                'response' => $response
            ]);
        } catch (Throwable) {
            // Ignore errors when writing logs
        }

        return $response;
    }
}
