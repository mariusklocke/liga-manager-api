<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL;

use GraphQL\Error\Error;
use GraphQL\Error\FormattedError;
use Psr\Log\LoggerInterface;

class ErrorHandler
{
    /** @var LoggerInterface */
    private $logger;

    /** @var AppContext */
    private $appContext;

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
        return array_map(function (Error $error) {

            $previous = $error->getPrevious();
            if ($previous instanceof \Throwable) {
                // Re-throw exception not caused in GraphQL library
                throw $previous;
            }

            $formatted = FormattedError::createFromException($error);

            $this->logger->notice('Handling expected exception in GraphQL library', [
                'exception' => $formatted,
                'user' => $this->getUserId()
            ]);

            return $formatted;
        }, $errors);
    }

    /**
     * @return string|null
     */
    private function getUserId(): ?string
    {
        return $this->appContext->isAuthenticated() ? $this->appContext->getAuthenticatedUser()->getId() : null;
    }
}