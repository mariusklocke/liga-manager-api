<?php declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure\API\GraphQL;

use GraphQL\Error\Error;
use GraphQL\Error\FormattedError;
use HexagonalPlayground\Domain\ExceptionInterface;
use Psr\Log\LoggerInterface;
use Throwable;

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
            $formatted = FormattedError::createFromException($error);

            $previous = $error->getPrevious();
            if ($previous instanceof ExceptionInterface) {
                $formatted['message'] = $previous->getMessage();
                $this->logger->notice('Handling expected uncaught exception', [
                    'exception' => $this->getExceptionContext($previous),
                    'user' => $this->getUserId()
                ]);
                return $formatted;
            }

            $this->logger->error('Unexpected exception', [
                'exception' => $this->getExceptionContext($previous ?? $error),
                'user' => $this->getUserId()
            ]);
            return $formatted;
        }, $errors);
    }

    /**
     * @param Throwable $throwable
     * @return array
     */
    private function getExceptionContext(Throwable $throwable): array
    {
        return [
            'class'   => get_class($throwable),
            'message' => $throwable->getMessage(),
            'code'    => $throwable->getCode(),
            'file'    => $throwable->getFile(),
            'line'    => $throwable->getLine(),
            'trace'   => $throwable->getTrace()
        ];
    }

    private function getUserId()
    {
        return $this->appContext->isAuthenticated() ? $this->appContext->getAuthenticatedUser()->getId() : null;
    }
}