<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API;

use HexagonalPlayground\Domain\ExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Slim\Exception\MethodNotAllowedException;
use Slim\Exception\NotFoundException as RouteNotFoundException;
use Throwable;

class ErrorHandler
{
    /** @var LoggerInterface */
    private $logger;

    /** @var JsonEncoder */
    private $jsonEncoder;

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
     * @return ResponseInterface
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response, Throwable $throwable): ResponseInterface
    {
        switch (true) {
            case ($throwable instanceof ExceptionInterface):
                $response = $this->createResponseFromException($throwable);
                break;
            case ($throwable instanceof RouteNotFoundException):
                $response = $this->createResponse(404, $this->getBody('Route not found'));
                break;
            case ($throwable instanceof MethodNotAllowedException):
                $message = 'HTTP Method not allowed. See Allow-Header for a list of allowed methods';
                $response = $this
                    ->createResponse(405, $this->getBody($message))
                    ->withHeader('Allow', implode(', ', $throwable->getAllowedMethods()));
                break;
            default:
                $response = $this->createResponse(500, $this->getBody('Internal Server Error'));
                break;
        }

        if ($response->getStatusCode() !== 500) {
            $this->logger->notice('Handling uncaught Exception', [
                'exception' => $this->getExceptionContext($throwable),
                'request' => $this->getRequestContext($request)
            ]);

            return $response;
        }

        $this->logger->error('Failed handling Exception. Internal Server Error', [
            'exception' => $this->getExceptionContext($throwable, true),
            'request' => $this->getRequestContext($request)
        ]);

        return $response;
    }

    /**
     * @param string $message
     * @return array
     */
    private function getBody(string $message): array
    {
        return [
            'errors' => [
                ['message' => $message]
            ]
        ];
    }

    /**
     * @param ExceptionInterface $exception
     * @return ResponseInterface
     */
    private function createResponseFromException(ExceptionInterface $exception): ResponseInterface
    {
        return $this->createResponse($exception->getHttpStatusCode(), $this->getBody($exception->getMessage()));
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
