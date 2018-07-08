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
use Slim\Http\Response;
use Slim\Interfaces\Http\HeadersInterface;
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
        $this->logger->notice('Handling uncaught Exception', [
            'exception' => $throwable,
            'request' => [
                'method' => $request->getMethod(),
                'uri' => $request->getUri()->__toString()
            ]
        ]);

        switch (true) {
            case ($throwable instanceof ExceptionInterface):
                return $this->createResponseFromException($throwable);
            case ($throwable instanceof RouteNotFoundException):
                return $this->createResponse(404, $throwable->getMessage());
            case ($throwable instanceof MethodNotAllowedException):
                $headers = new Headers(['Allow' => implode(', ', $throwable->getAllowedMethods())]);
                $message = 'See Allow-Header for a list of allowed methods';
                return $this->createResponse(405, $message, $headers);
        }

        $this->logger->error('Failed handling Exception. Internal Server Error', [
            'exception' => $throwable,
            'request' => [
                'method' => $request->getMethod(),
                'uri' => $request->getUri()->__toString()
            ]
        ]);
        return $this->createResponse(500, '');
    }

    /**
     * @param int $statusCode
     * @param string $message
     * @param HeadersInterface|null $headers
     * @return Response
     */
    private function createResponse(int $statusCode, string $message, HeadersInterface $headers = null): Response
    {
        $response = new Response($statusCode, $headers);
        return $response->withJson([
            'title'   => $response->getReasonPhrase(),
            'message' => $message
        ]);
    }

    /**
     * @param ExceptionInterface $exception
     * @return Response
     */
    private function createResponseFromException(ExceptionInterface $exception): Response
    {
        $response = new Response($exception->getHttpStatusCode());
        return $response->withJson([
            'title' => $response->getReasonPhrase(),
            'message' => $exception->getMessage()
        ]);
    }
}
