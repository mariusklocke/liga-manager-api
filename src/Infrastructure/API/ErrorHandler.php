<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API;

use HexagonalPlayground\Domain\ExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Slim\Exception\MethodNotAllowedException;
use Slim\Exception\NotFoundException as RouteNotFoundException;
use Slim\Http\Headers;
use Throwable;

class ErrorHandler
{
    use ResponseFactoryTrait;

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
     * @return ResponseInterface
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response, Throwable $throwable): ResponseInterface
    {
        $this->logger->notice('Handling uncaught Exception', [
            'exception' => $this->getExceptionContext($throwable),
            'request' => [
                'method' => $request->getMethod(),
                'uri' => $this->getSafeUri($request)
            ]
        ]);

        switch (true) {
            case ($throwable instanceof ExceptionInterface):
                return $this->createResponseFromException($throwable);
            case ($throwable instanceof RouteNotFoundException):
                return $this->createResponse(404, $this->getBody('Route not found'));
            case ($throwable instanceof MethodNotAllowedException):
                $headers = new Headers(['Allow' => implode(', ', $throwable->getAllowedMethods())]);
                $message = 'HTTP Method not allowed. See Allow-Header for a list of allowed methods';
                return $this->createResponse(405, $this->getBody($message), $headers);
        }

        $this->logger->error('Failed handling Exception. Internal Server Error', [
            'exception' => $this->getExceptionContext($throwable, true),
            'request' => [
                'method' => $request->getMethod(),
                'uri' => $this->getSafeUri($request)
            ]
        ]);
        return $this->createResponse(500, $this->getBody('Unhandled exception'));
    }

    /**
     * @param string $message
     * @return array
     */
    private function getBody(string $message): array
    {
        return ['message' => $message];
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
     * @return string
     */
    private function getSafeUri(RequestInterface $request): string
    {
        return $request->getUri()->withUserInfo('', '')->__toString();
    }
}
