<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL;

use GraphQL\Error\Error;
use GraphQL\Error\FormattedError;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;
use Throwable;

class ErrorHandler
{
    /** @var LoggerInterface */
    private $logger;

    /** @var AppContext */
    private $appContext;

    private static $loggableHeaders = [
        'Content-Length',
        'Content-Type',
        'User-Agent'
    ];

    /**
     * @param LoggerInterface $logger
     * @param AppContext $appContext
     */
    public function __construct(LoggerInterface $logger, AppContext $appContext)
    {
        $this->logger = $logger;
        $this->appContext = $appContext;
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
            'request' => $this->getRequestContext($this->appContext->getRequest())
        ]);

        return $formatted;
    }

    /**
     * @param RequestInterface $request
     * @return array
     */
    private function getRequestContext(RequestInterface $request): array
    {
        $headers = [];
        foreach (self::$loggableHeaders as $headerName) {
            $headers[$headerName] = $request->getHeader($headerName);
        }

        return [
            'httpVersion' => $request->getProtocolVersion(),
            'method' => $request->getMethod(),
            'uri' => $request->getUri()->withUserInfo('', '')->__toString(),
            'headers' => $headers
        ];
    }
}