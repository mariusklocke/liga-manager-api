<?php
declare(strict_types=1);

namespace HexagonalPlayground\Infrastructure;

use Exception;
use Psr\Log\LoggerInterface;

class Retry
{
    private LoggerInterface $logger;
    private int $timeout;
    private int $sleepTime;

    public function __construct(LoggerInterface $logger, int $timeout, int $sleepTime = 0)
    {
        $this->logger = $logger;
        $this->timeout = $timeout;
        $this->sleepTime = $sleepTime;
    }

    public function __invoke(callable $callable)
    {
        $attempt = 1;
        $startTime = time();

        do {
            try {
                return $callable();
            } catch (Exception $exception) {
                sleep($this->sleepTime);
                if (time() - $startTime < $this->timeout) {
                    $this->logger->warning($exception->getMessage(), [
                        'attempt' => $attempt
                    ]);
                } else {
                    throw $exception;
                }
                $attempt++;
            }
        } while (true);
    }
}
