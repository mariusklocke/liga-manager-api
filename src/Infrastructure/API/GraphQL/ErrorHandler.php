<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL;

use GraphQL\Error\Error;
use GraphQL\Error\FormattedError;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Throwable;

class ErrorHandler
{
    /** @var LoggerInterface */
    private $logger;

    /** @var ServerRequestInterface */
    private $request;

    private static $loggableHeaders = [
        'Content-Length',
        'Content-Type',
        'User-Agent'
    ];

    /**
     * @param LoggerInterface $logger
     * @param ServerRequestInterface $request
     */
    public function __construct(LoggerInterface $logger, ServerRequestInterface $request)
    {
        $this->logger = $logger;
        $this->request = $request;
    }

    /**
     * @param Error[] $errors
     * @return array
     */
    public function __invoke(array $errors): array
    {
        $formatted = [];
        foreach ($errors as $error) {
            $previous = $error->getPrevious();
            if ($previous instanceof Throwable) {
                // Re-throw exception not caused in GraphQL library
                throw $previous;
            }

            $formatted[] = FormattedError::createFromException($error);
        }

        $this->logger->notice('Handling exception in GraphQL library', [
            'errors' => $formatted,
            'request' => $this->getRequestContext()
        ]);

        return $formatted;
    }

    /**
     * @return array
     */
    private function getRequestContext(): array
    {
        $body = $this->request->getParsedBody();

        $headers = [];
        foreach (self::$loggableHeaders as $headerName) {
            $headers[$headerName] = $this->request->getHeader($headerName);
        }

        return [
            'httpVersion' => $this->request->getProtocolVersion(),
            'method' => $this->request->getMethod(),
            'uri' => $this->request->getUri()->withUserInfo('', '')->__toString(),
            'headers' => $headers,
            'body' => [
                'query' => $body['query'] ?? null
            ]
        ];
    }
}