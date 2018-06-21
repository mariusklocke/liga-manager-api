<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API;

use HexagonalPlayground\Application\Exception\AuthenticationException;
use HexagonalPlayground\Application\Exception\PermissionException;
use HexagonalPlayground\Application\Exception\NotFoundException;
use HexagonalPlayground\Application\Exception\UniquenessException;
use HexagonalPlayground\Domain\DomainException;
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
            case ($throwable instanceof HttpException):
                return $this->createResponseFromHttpException($throwable);
            case ($throwable instanceof AuthenticationException):
                return $this->createResponse(401, 'Unauthorized', $throwable->getMessage());
            case ($throwable instanceof PermissionException):
                return $this->createResponse(403, 'Forbidden', $throwable->getMessage());
            case ($throwable instanceof RouteNotFoundException):
            case ($throwable instanceof NotFoundException):
                return $this->createResponse(404, 'Not Found', $throwable->getMessage());
            case ($throwable instanceof DomainException):
            case ($throwable instanceof UniquenessException):
                return $this->createResponse(400, 'Bad Request', $throwable->getMessage());
            case ($throwable instanceof MethodNotAllowedException):
                $headers = new Headers(['Allow' => implode(', ', $throwable->getAllowedMethods())]);
                $message = 'See Allow-Header for a list of allowed methods';
                return $this->createResponse(405, 'Method not allowed', $message, $headers);
        }

        $this->logger->error('Failed handling Exception. Internal Server Error', [
            'exception' => $throwable,
            'request' => [
                'method' => $request->getMethod(),
                'uri' => $request->getUri()->__toString()
            ]
        ]);
        return $this->createResponse(500, 'Internal Server Error', '');
    }

    /**
     * @param int $statusCode
     * @param string $title
     * @param string $message
     * @param HeadersInterface|null $headers
     * @return Response
     */
    private function createResponse(int $statusCode, string $title, string $message, HeadersInterface $headers = null): Response
    {
        return (new Response($statusCode, $headers))->withJson([
            'title'   => $title,
            'message' => $message
        ]);
    }

    /**
     * @param HttpException $exception
     * @return Response
     */
    private function createResponseFromHttpException(HttpException $exception): Response
    {
        return (new Response($exception->getCode()))->withJson($exception);
    }
}
