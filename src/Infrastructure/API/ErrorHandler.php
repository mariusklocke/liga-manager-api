<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API;

use HexagonalPlayground\Domain\Exception\ExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Slim\Exception\HttpMethodNotAllowedException;
use Slim\Exception\HttpNotFoundException;
use Slim\Interfaces\ErrorHandlerInterface;
use Throwable;

class ErrorHandler implements ErrorHandlerInterface
{
    /** @var LoggerInterface */
    private LoggerInterface $logger;

    /** @var ResponseFactoryInterface */
    private ResponseFactoryInterface $responseFactory;

    /** @var JsonResponseWriter */
    private JsonResponseWriter $responseWriter;

    /**
     * @param LoggerInterface $logger
     * @param ResponseFactoryInterface $responseFactory
     * @param JsonResponseWriter $responseWriter
     */
    public function __construct(LoggerInterface $logger, ResponseFactoryInterface $responseFactory, JsonResponseWriter $responseWriter)
    {
        $this->logger = $logger;
        $this->responseFactory = $responseFactory;
        $this->responseWriter = $responseWriter;
    }

    /**
     * @param ServerRequestInterface $request
     * @param Throwable $exception
     * @param bool $displayErrorDetails
     * @param bool $logErrors
     * @param bool $logErrorDetails
     * @return ResponseInterface
     */
    public function __invoke(
        ServerRequestInterface $request,
        Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails
    ): ResponseInterface {
        switch (true) {
            case ($exception instanceof ExceptionInterface):
                $response = $this->createResponse($exception->getHttpResponseCode(), $exception->getMessage(), $exception->getCode());
                break;
            case ($exception instanceof HttpNotFoundException):
                $response = $this->createResponse(404, 'Route not found', 'ERR-NOT-FOUND');
                break;
            case ($exception instanceof HttpMethodNotAllowedException):
                $message = 'HTTP Method not allowed. See Allow-Header for a list of allowed methods';
                $response = $this
                    ->createResponse(405, $message, 'ERR-METHOD-NOT-ALLOWED')
                    ->withHeader('Allow', implode(', ', $exception->getAllowedMethods()));
                break;
            default:
                $response = $this->createResponse(500, 'Internal Server Error', 'ERR-INTERNAL');
                break;
        }

        $type = get_class($exception);
        $message = $exception->getMessage();

        if ($response->getStatusCode() !== 500) {
            $this->logger->notice("Handling $type: $message", [
                'exception' => $this->getExceptionContext($exception),
                'request' => $this->getRequestContext($request)
            ]);

            return $response;
        }

        $this->logger->error("Unhandled $type: $message", [
            'exception' => $this->getExceptionContext($exception, true),
            'request' => $this->getRequestContext($request)
        ]);

        return $response;
    }

    /**
     * @param int $statusCode
     * @param string $message
     * @param string $errorCode
     * @return ResponseInterface
     */
    private function createResponse(int $statusCode, string $message, string $errorCode): ResponseInterface
    {
        $response = $this->responseFactory->createResponse($statusCode);

        return $this->responseWriter->write($response, [
            'errors' => [
                [
                    'code' => $errorCode,
                    'message' => $message
                ]
            ]
        ]);
    }

    /**
     * @param Throwable $throwable
     * @param bool $includeTrace
     * @return array
     */
    private function getExceptionContext(Throwable $throwable, bool $includeTrace = false): array
    {
        $context = [
            'class'   => get_class($throwable),
            'message' => $throwable->getMessage(),
            'code'    => $throwable->getCode(),
            'file'    => $throwable->getFile(),
            'line'    => $throwable->getLine()
        ];
        if ($includeTrace) {
            $context['trace'] = $throwable->getTrace();
        }

        return $context;
    }

    /**
     * @param RequestInterface $request
     * @return array
     */
    private function getRequestContext(RequestInterface $request): array
    {
        return [
            'method' => $request->getMethod(),
            'uri' => $request->getUri()->withUserInfo('', '')->__toString()
        ];
    }
}
