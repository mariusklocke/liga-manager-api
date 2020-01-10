<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API;

use HexagonalPlayground\Domain\ExceptionInterface;
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
    use JsonEncodingTrait;

    /** @var LoggerInterface */
    private $logger;

    /** @var ResponseFactoryInterface */
    private $responseFactory;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger, ResponseFactoryInterface $responseFactory)
    {
        $this->logger = $logger;
        $this->responseFactory = $responseFactory;
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
    ): ResponseInterface
    {
        switch (true) {
            case ($exception instanceof ExceptionInterface):
                $response = $this->createResponse($this->mapCode($exception), $exception->getMessage());
                break;
            case ($exception instanceof HttpNotFoundException):
                $response = $this->createResponse(404, 'Route not found');
                break;
            case ($exception instanceof HttpMethodNotAllowedException):
                $message = 'HTTP Method not allowed. See Allow-Header for a list of allowed methods';
                $response = $this
                    ->createResponse(405, $message)
                    ->withHeader('Allow', implode(', ', $exception->getAllowedMethods()));
                break;
            default:
                $response = $this->createResponse(500, 'Internal Server Error');
                break;
        }

        if ($response->getStatusCode() !== 500) {
            $this->logger->notice('Handling uncaught Exception', [
                'exception' => $this->getExceptionContext($exception),
                'request' => $this->getRequestContext($request)
            ]);

            return $response;
        }

        $this->logger->error('Failed handling Exception. Internal Server Error', [
            'exception' => $this->getExceptionContext($exception, true),
            'request' => $this->getRequestContext($request)
        ]);

        return $response;
    }

    /**
     * Returns the appropriate HTTP status code for a given exception
     *
     * @param ExceptionInterface $exception
     * @return int
     */
    private function mapCode(ExceptionInterface $exception): int
    {
        switch ($exception->getCode()) {
            case 'ERR-NOT-FOUND':
                return 404;
            case 'ERR-PERMISSION':
                return 403;
            case 'ERR-AUTHENTICATION':
                return 401;
            case 'ERR-DOMAIN':
            case 'ERR-INVALID-INPUT':
            case 'ERR-UNIQUENESS':
                return 400;
            default:
                return 500;
        }
    }

    /**
     * @param int $statusCode
     * @param string $message
     * @return ResponseInterface
     */
    private function createResponse(int $statusCode, string $message): ResponseInterface
    {
        $response = $this->responseFactory->createResponse($statusCode);
        $response = $response->withHeader('Content-Type', 'application/json');

        return $this->toJson($response, [
            'errors' => [
                [
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
            $context['trace'] = $throwable->getTraceAsString();
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
